<?php

namespace App\Services\Webhook\Stripe;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;

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
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;

            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($event['data']['object']);
                break;

            default:
                Log::info('Received unhandled Stripe webhook event', [
                    'type' => $event['type'],
                    'id' => $event['id'],
                ]);
        }
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

        // Mark donation as paid
        $donation->markAsPaid();

        Log::info('Donation marked as paid via webhook', [
            'donation_id' => $donation->id,
            'session_id' => $session['id'],
            'amount_cents' => $donation->amount_cents,
            'pet_id' => $donation->pet_id,
            'user_id' => $donation->user_id,
        ]);
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
