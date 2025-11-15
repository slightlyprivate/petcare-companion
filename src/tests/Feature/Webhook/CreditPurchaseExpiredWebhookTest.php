<?php

namespace Tests\Feature\Webhook;

use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Models\User;
use Tests\Support\Webhook\TestableStripeWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditPurchaseExpiredWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Provide fake Stripe keys to satisfy any incidental Stripe usage
        config([
            'services.stripe.secret' => 'sk_test_fake',
            'services.stripe.webhook.secret' => 'whsec_test',
        ]);
    }

    public function test_expired_checkout_session_marks_credit_purchase_failed(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance_credits' => 0]);

        $bundle = CreditBundle::create([
            'name' => 'Test Bundle',
            'credits' => 100,
            'price_cents' => 2000,
        ]);

        $purchase = CreditPurchase::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => 100,
            'amount_cents' => 2000,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_expired_123',
        ]);

        // Create a testable webhook service exposing the expired handler
        $service = new TestableStripeWebhookService();

        $session = [
            'id' => $purchase->stripe_session_id,
            'metadata' => [
                'purchase_id' => $purchase->id,
            ],
        ];

        // Act: simulate Stripe sending checkout.session.expired for this session
        $service->triggerExpired($session);

        $this->assertEquals('failed', $purchase->fresh()->status);
    }
}
