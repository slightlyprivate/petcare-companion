<?php

namespace Tests\Support\Webhook;

use App\Services\Webhook\Stripe\StripeWebhookService;

/**
 * Test helper to expose protected handlers for Stripe webhooks.
 */
class TestableStripeWebhookService extends StripeWebhookService
{
    public function triggerCompleted(array $session): void
    {
        $this->handleCheckoutSessionCompleted($session);
    }

    public function triggerExpired(array $session): void
    {
        $this->handleCheckoutSessionExpired($session);
    }
}
