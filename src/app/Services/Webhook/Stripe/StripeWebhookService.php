<?php

namespace App\Services\Webhook\Stripe;

use App\Helpers\NotificationHelper;
use App\Models\Gift;
use App\Notifications\Gift\GiftSuccessNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Service for handling Stripe webhooks.
 */
class StripeWebhookService
{
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
        $giftId = $session['metadata']['gift_id'] ?? null;

        if (! $giftId) {
            Log::warning('Checkout session completed without gift_id in metadata', [
                'session_id' => $session['id'],
            ]);

            return;
        }

        $gift = Gift::where('stripe_session_id', $session['id'])->first();

        if (! $gift) {
            Log::warning('Gift not found for completed checkout session', [
                'session_id' => $session['id'],
                'gift_id' => $giftId,
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

        // Send gift success notification to user if enabled
        if (NotificationHelper::isNotificationEnabled($gift->user, 'gift')) {
            Notification::send($gift->user, new GiftSuccessNotification($gift));
        }

        Log::info('Gift marked as paid via webhook', [
            'gift_id' => $gift->id,
            'session_id' => $session['id'],
            'cost_in_credits' => $gift->cost_in_credits,
            'pet_id' => $gift->pet_id,
            'user_id' => $gift->user_id,
        ]);
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
}
