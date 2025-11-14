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

        // Retrieve payment intent to get charge details
        $metadata = $this->extractChargeMetadata($session);

        // Update gift with charge metadata
        if ($session['payment_intent']) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $paymentIntent = \Stripe\PaymentIntent::retrieve($session['payment_intent']);

                if ($paymentIntent->charges && $paymentIntent->charges->count() > 0) {
                    $charge = $paymentIntent->charges->first();
                    $gift->stripe_charge_id = $charge->id;
                    $metadata = $this->extractChargeMetadata((array) $charge);
                }
            } catch (\Exception $e) {
                Log::error('Error retrieving charge metadata', [
                    'gift_id' => $gift->id,
                    'error' => $e->getMessage(),
                ]);
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
}
