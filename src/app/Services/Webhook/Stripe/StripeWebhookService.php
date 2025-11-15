<?php

namespace App\Services\Webhook\Stripe;

use App\Constants\CreditConstants;
use App\Helpers\NotificationHelper;
use App\Models\CreditPurchase;
use App\Models\Gift;
use App\Models\User;
use App\Notifications\Gift\GiftSuccessNotification;
use App\Services\Credit\CreditPurchaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Service for handling Stripe webhooks.
 */
class StripeWebhookService
{
    private CreditPurchaseService $creditPurchaseService;

    public function __construct(?CreditPurchaseService $creditPurchaseService = null)
    {
        $this->creditPurchaseService = $creditPurchaseService ?? new CreditPurchaseService;
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handle(string $payload, string $sigHeader = ''): void
    {
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Verify webhook signature
        try {
            $event = $this->verifyWebhookSignature(
                $payload,
                $sigHeader
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'signature' => $sigHeader,
            ]);
            throw $e;
        }

        // Handle the event
        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($this->toArray($event['data']['object']));
                break;

            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($this->toArray($event['data']['object']));
                break;

            default:
                Log::info('Received unhandled Stripe webhook event', [
                    'type' => $event['type'],
                    'id' => $event['id'],
                ]);
        }
    }

    /**
     * Convert Stripe objects to arrays recursively.
     */
    protected function toArray(mixed $value): mixed
    {
        if (\is_array($value)) {
            return \array_map([$this, 'toArray'], $value);
        }

        if ($value instanceof \Stripe\StripeObject) {
            return \array_map([$this, 'toArray'], $value->toArray());
        }

        return $value;
    }

    /**
     * Handle successful checkout session completion.
     */
    protected function handleCheckoutSessionCompleted(array $session): void
    {
        // Check if this is a credit purchase
        $purchaseId = $session['metadata']['purchase_id'] ?? null;
        if ($purchaseId) {
            $this->handleCreditPurchaseCompletion($session);

            return;
        }

        // Otherwise handle as gift
        $giftId = $session['metadata']['gift_id'] ?? null;

        $gift = $giftId ? Gift::find($giftId) : null;

        // If gift not found by ID metadata, attempt fallback identification
        if (! $gift) {
            $gift = $this->findGiftByFallback($session);
        }

        if (! $gift) {
            Log::warning('Gift not found via primary or fallback lookup', [
                'session_id' => $session['id'],
                'gift_id_metadata' => $giftId,
                'client_reference_id' => $session['client_reference_id'] ?? null,
            ]);

            return;
        }

        if ($gift->status === 'paid') {
            Log::info('Gift already marked as paid', [
                'gift_id' => $gift->id,
                'session_id' => $session['id'],
            ]);

            return;
        }

        // Retrieve payment intent to get charge details with retry logic
        $metadata = $this->extractChargeMetadata($session);

        // Update gift with charge metadata
        if ($session['payment_intent']) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

                // Attempt to retrieve payment intent with retry logic (max 3 attempts)
                $paymentIntent = $this->retrievePaymentIntentWithRetry($session['payment_intent'], 3);

                if ($paymentIntent && $paymentIntent->charges && $paymentIntent->charges->count() > 0) {
                    $charge = $paymentIntent->charges->first();
                    $gift->stripe_charge_id = $charge->id;
                    $metadata = $this->extractChargeMetadata((array) $charge);
                    Log::info('Successfully retrieved charge metadata via retry', [
                        'gift_id' => $gift->id,
                        'charge_id' => $charge->id,
                        'payment_intent' => $session['payment_intent'],
                    ]);
                } else {
                    Log::warning('No charges found for payment intent', [
                        'gift_id' => $gift->id,
                        'payment_intent' => $session['payment_intent'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to retrieve charge metadata after all retries', [
                    'gift_id' => $gift->id,
                    'payment_intent' => $session['payment_intent'],
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ]);
                // Continue with partial metadata - receipt rendering will validate completeness
            }
        }

        $gift->stripe_metadata = $metadata;
        $gift->markAsPaid();

        // Do NOT deduct wallet credits for Stripe-paid gifts.
        // Stripe has already charged the user for this gift; wallet-based gifts
        // are handled at creation time via PetGiftService and do the deduction there.

        // Send gift success notification to user if enabled
        if (NotificationHelper::isNotificationEnabled($gift->user, 'gift')) {
            Notification::send($gift->user, new GiftSuccessNotification($gift));
        }

        Log::info('Gift marked as paid via webhook (no wallet deduction)', [
            'gift_id' => $gift->id,
            'session_id' => $session['id'],
            'cost_in_credits' => $gift->cost_in_credits,
            'pet_id' => $gift->pet_id,
            'user_id' => $gift->user_id,
        ]);
    }

    /**
     * Handle credit purchase completion via webhook.
     */
    protected function handleCreditPurchaseCompletion(array $session): void
    {
        $purchaseId = $session['metadata']['purchase_id'] ?? null;
        $purchase = $purchaseId ? CreditPurchase::find($purchaseId) : null;

        // If purchase not found by ID metadata, attempt fallback identification
        if (! $purchase) {
            $purchase = $this->findCreditPurchaseByFallback($session);
        }

        if (! $purchase) {
            Log::warning('Credit purchase not found via primary or fallback lookup', [
                'session_id' => $session['id'],
                'purchase_id_metadata' => $purchaseId,
                'client_reference_id' => $session['client_reference_id'] ?? null,
            ]);

            return;
        }

        if ($purchase->status === 'completed') {
            Log::info('Credit purchase already marked as completed', [
                'purchase_id' => $purchase->id,
                'session_id' => $session['id'],
            ]);

            return;
        }

        try {
            $chargeId = null;

            // Retrieve charge ID from payment intent if available
            if ($session['payment_intent']) {
                try {
                    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                    $paymentIntent = $this->retrievePaymentIntentWithRetry($session['payment_intent'], 3);

                    if ($paymentIntent && $paymentIntent->charges && $paymentIntent->charges->count() > 0) {
                        $chargeId = $paymentIntent->charges->first()->id;
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not retrieve charge ID for credit purchase', [
                        'purchase_id' => $purchase->id,
                        'payment_intent' => $session['payment_intent'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Complete the purchase and update wallet
            $this->creditPurchaseService->completePurchase($session['id'], $chargeId);

            Log::info('Credit purchase completed and wallet updated via webhook', [
                'purchase_id' => $purchase->id,
                'session_id' => $session['id'],
                'credits' => $purchase->credits,
                'user_id' => $purchase->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to complete credit purchase via webhook', [
                'purchase_id' => $purchase->id,
                'session_id' => $session['id'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extract relevant charge metadata for storage.
     */
    protected function extractChargeMetadata(array $charge): array
    {
        return [
            'amount' => $charge['amount'] ?? null,
            'amount_captured' => $charge['amount_captured'] ?? null,
            'currency' => $charge['currency'] ?? 'usd',
            'payment_method' => $charge['payment_method_details']['type'] ?? $charge['payment_method'] ?? null,
            'brand' => $charge['payment_method_details']['card']['brand'] ?? null,
            'last4' => $charge['payment_method_details']['card']['last4'] ?? null,
            'exp_month' => $charge['payment_method_details']['card']['exp_month'] ?? null,
            'exp_year' => $charge['payment_method_details']['card']['exp_year'] ?? null,
            'created' => $charge['created'] ?? now()->timestamp,
            'receipt_url' => $charge['receipt_url'] ?? null,
        ];
    }

    /**
     * Handle expired checkout session.
     */
    protected function handleCheckoutSessionExpired(array $session): void
    {
        $giftId = $session['metadata']['gift_id'] ?? null;

        if (! $giftId) {
            Log::warning('Checkout session expired without gift_id in metadata', [
                'session_id' => $session['id'],
            ]);

            return;
        }

        $gift = Gift::where('stripe_session_id', $session['id'])->first();

        if (! $gift) {
            Log::warning('Gift not found for expired checkout session', [
                'session_id' => $session['id'],
                'gift_id' => $giftId,
            ]);

            return;
        }

        if ($gift->status !== 'pending') {
            Log::info('Gift not pending, skipping expiration handling', [
                'gift_id' => $gift->id,
                'session_id' => $session['id'],
                'current_status' => $gift->status,
            ]);

            return;
        }

        // Mark gift as failed due to expiration
        $gift->markAsFailed();

        Log::info('Gift marked as failed due to session expiration', [
            'gift_id' => $gift->id,
            'session_id' => $session['id'],
        ]);
    }

    /**
     * Verify the webhook signature.
     */
    protected function verifyWebhookSignature(string $payload, string $sigHeader): \Stripe\Event
    {
        $endpointSecret = config('services.stripe.webhook.secret');
        try {
            return \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'signature' => $sigHeader,
            ]);

            throw $e;
        }
    }

    /**
     * Retrieve payment intent from Stripe with exponential backoff retry logic.
     *
     * Retries up to maxRetries times if the Stripe API is temporarily unavailable.
     * Uses exponential backoff: 1s, 2s, 4s between retries.
     *
     * @param  string  $paymentIntentId  The Stripe payment intent ID
     * @param  int  $maxRetries  Maximum number of retry attempts
     * @return \Stripe\PaymentIntent|null Payment intent or null if all retries fail
     */
    protected function retrievePaymentIntentWithRetry(string $paymentIntentId, int $maxRetries = 3): ?\Stripe\PaymentIntent
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                return \Stripe\PaymentIntent::retrieve($paymentIntentId);
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                // Transient API connection error - retry
                $lastException = $e;
                $attempt++;

                if ($attempt < $maxRetries) {
                    // Exponential backoff: 1s, 2s, 4s
                    $backoffSeconds = (2 ** ($attempt - 1));
                    Log::warning('Stripe API connection error, retrying', [
                        'payment_intent' => $paymentIntentId,
                        'attempt' => $attempt,
                        'max_retries' => $maxRetries,
                        'backoff_seconds' => $backoffSeconds,
                        'error' => $e->getMessage(),
                    ]);
                    sleep($backoffSeconds);
                }
            } catch (\Stripe\Exception\RateLimitException $e) {
                // Rate limited - retry with longer backoff
                $lastException = $e;
                $attempt++;

                if ($attempt < $maxRetries) {
                    $backoffSeconds = (2 ** $attempt); // Longer backoff for rate limits
                    Log::warning('Stripe rate limit reached, retrying', [
                        'payment_intent' => $paymentIntentId,
                        'attempt' => $attempt,
                        'max_retries' => $maxRetries,
                        'backoff_seconds' => $backoffSeconds,
                    ]);
                    sleep($backoffSeconds);
                }
            } catch (\Exception $e) {
                // Non-retryable error (auth, not found, etc.)
                Log::error('Non-retryable Stripe error', [
                    'payment_intent' => $paymentIntentId,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ]);

                return null;
            }
        }

        // All retries exhausted
        Log::error('Failed to retrieve payment intent after all retries', [
            'payment_intent' => $paymentIntentId,
            'max_retries' => $maxRetries,
            'last_error' => $lastException?->getMessage(),
        ]);

        return null;
    }

    /**
     * Deduct credits from user's wallet and create a transaction record.
     *
     * Uses atomic operations to prevent race conditions. Checks if credits have
     * already been deducted to prevent double-deduction on webhook retry.
     */
    protected function deductCreditsFromWallet(User $user, int $credits, ?string $giftId = null): void
    {
        // Get wallet with lock to prevent concurrent modification
        $wallet = $user->wallet()->lockForUpdate()->first();

        if (! $wallet) {
            Log::warning('Wallet not found for user', ['user_id' => $user->id]);

            return;
        }

        // Check if transaction already exists for this webhook to prevent double-deduction
        // In case of webhook retry
        $existingTransaction = $wallet->transactions()
            ->where('amount_credits', $credits)
            ->where('reason', 'gift_sent')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if ($existingTransaction) {
            Log::info('Gift credit deduction already processed', [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'credits' => $credits,
            ]);

            return;
        }

        // Decrement wallet balance
        $wallet->decrement('balance_credits', $credits);

        // Log the transaction using centralized credit-to-cents conversion
        $wallet->transactions()->create([
            'amount' => CreditConstants::toCents($credits),
            'type' => 'debit',
            'amount_credits' => $credits,
            'reason' => 'gift_sent',
            'related_type' => 'gift',
            'related_id' => $giftId,
        ]);
    }

    /**
     * Find Gift using fallback identification when metadata is missing.
     *
     * Attempts to identify gift using (in order):
     * 1. Session ID lookup (primary fallback)
     * 2. Client reference ID lookup (user-created gift ID)
     */
    protected function findGiftByFallback(array $session): ?Gift
    {
        $sessionId = $session['id'];
        $clientRefId = $session['client_reference_id'] ?? null;

        // Attempt 1: Look up by session ID (most reliable)
        $gift = Gift::where('stripe_session_id', $sessionId)->first();
        if ($gift) {
            Log::info('Gift found via session ID fallback', [
                'gift_id' => $gift->id,
                'session_id' => $sessionId,
            ]);

            return $gift;
        }

        // Attempt 2: Look up by client reference ID if available
        if ($clientRefId) {
            $gift = Gift::find($clientRefId);
            if ($gift) {
                Log::info('Gift found via client_reference_id fallback', [
                    'gift_id' => $gift->id,
                    'client_reference_id' => $clientRefId,
                    'session_id' => $sessionId,
                ]);

                return $gift;
            }
        }

        return null;
    }

    /**
     * Find CreditPurchase using fallback identification when metadata is missing.
     *
     * Attempts to identify purchase using (in order):
     * 1. Session ID lookup (primary fallback)
     * 2. Client reference ID lookup (purchase ID stored by client)
     */
    protected function findCreditPurchaseByFallback(array $session): ?CreditPurchase
    {
        $sessionId = $session['id'];
        $clientRefId = $session['client_reference_id'] ?? null;

        // Attempt 1: Look up by session ID (most reliable)
        $purchase = CreditPurchase::where('stripe_session_id', $sessionId)->first();
        if ($purchase) {
            Log::info('Credit purchase found via session ID fallback', [
                'purchase_id' => $purchase->id,
                'session_id' => $sessionId,
            ]);

            return $purchase;
        }

        // Attempt 2: Look up by client reference ID if available
        if ($clientRefId) {
            $purchase = CreditPurchase::find($clientRefId);
            if ($purchase) {
                Log::info('Credit purchase found via client_reference_id fallback', [
                    'purchase_id' => $purchase->id,
                    'client_reference_id' => $clientRefId,
                    'session_id' => $sessionId,
                ]);

                return $purchase;
            }
        }

        return null;
    }
}
