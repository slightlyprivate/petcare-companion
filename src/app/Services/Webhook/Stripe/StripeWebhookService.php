<?php

namespace App\Services\Webhook\Stripe;

use App\Helpers\NotificationHelper;
use App\Models\Donation;
use App\Notifications\Donation\DonationSuccessNotification;
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
     *
     * @param  mixed  $value
     * @return mixed
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
        $donationId = $session['metadata']['donation_id'] ?? null;

        if (! $donationId) {
            Log::warning('Checkout session completed without donation_id in metadata', [
                'session_id' => $session['id'],
            ]);

            return;
        }

        $donation = Donation::where('stripe_session_id', $session['id'])->first();

        if (! $donation) {
            Log::warning('Donation not found for completed checkout session', [
                'session_id' => $session['id'],
                'donation_id' => $donationId,
            ]);

            return;
        }

        if ($donation->status === 'paid') {
            Log::info('Donation already marked as paid', [
                'donation_id' => $donation->id,
                'session_id' => $session['id'],
            ]);

            return;
        }

        // Retrieve payment intent to get charge details
        $metadata = $this->extractChargeMetadata($session);

        // Update donation with charge metadata
        if ($session['payment_intent']) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $paymentIntent = \Stripe\PaymentIntent::retrieve($session['payment_intent']);

                if ($paymentIntent->charges && $paymentIntent->charges->count() > 0) {
                    $charge = $paymentIntent->charges->first();
                    $donation->stripe_charge_id = $charge->id;
                    $metadata = $this->extractChargeMetadata((array) $charge);
                }
            } catch (\Exception $e) {
                Log::error('Error retrieving charge metadata', [
                    'donation_id' => $donation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $donation->stripe_metadata = $metadata;
        $donation->markAsPaid();

        // Send donation success notification to user if enabled
        if (NotificationHelper::isNotificationEnabled($donation->user, 'donation')) {
            Notification::send($donation->user, new DonationSuccessNotification($donation));
        }

        Log::info('Donation marked as paid via webhook', [
            'donation_id' => $donation->id,
            'session_id' => $session['id'],
            'amount_cents' => $donation->amount_cents,
            'pet_id' => $donation->pet_id,
            'user_id' => $donation->user_id,
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
        $donationId = $session['metadata']['donation_id'] ?? null;

        if (! $donationId) {
            Log::warning('Checkout session expired without donation_id in metadata', [
                'session_id' => $session['id'],
            ]);

            return;
        }

        $donation = Donation::where('stripe_session_id', $session['id'])->first();

        if (! $donation) {
            Log::warning('Donation not found for expired checkout session', [
                'session_id' => $session['id'],
                'donation_id' => $donationId,
            ]);

            return;
        }

        if ($donation->status !== 'pending') {
            Log::info('Donation not pending, skipping expiration handling', [
                'donation_id' => $donation->id,
                'session_id' => $session['id'],
                'current_status' => $donation->status,
            ]);

            return;
        }

        // Mark donation as failed due to expiration
        $donation->markAsFailed();

        Log::info('Donation marked as failed due to session expiration', [
            'donation_id' => $donation->id,
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
