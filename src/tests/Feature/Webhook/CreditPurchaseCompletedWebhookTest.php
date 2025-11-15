<?php

namespace Tests\Feature\Webhook;

use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Webhook\TestableStripeWebhookService;
use Tests\TestCase;

class CreditPurchaseCompletedWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_session_completed_increments_wallet_and_logs_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create(['balance_credits' => 0]);

        $bundle = CreditBundle::create([
            'name' => 'E2E Bundle',
            'credits' => 100,
            'price_cents' => 2000,
        ]);

        $purchase = CreditPurchase::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => 100,
            'amount_cents' => 2000,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_complete_123',
        ]);

        // Use a testable subclass to expose the protected handler directly
        $service = new TestableStripeWebhookService;

        $session = [
            'id' => $purchase->stripe_session_id,
            'metadata' => [
                'purchase_id' => $purchase->id,
            ],
            // No payment_intent: we don't need to retrieve charge for this test
        ];

        // Act
        $service->triggerCompleted($session);

        // Assert wallet incremented
        $this->assertEquals(100, $wallet->fresh()->balance_credits);

        // Assert purchase completed
        $this->assertEquals('completed', $purchase->fresh()->status);

        // Assert transaction recorded
        $this->assertDatabaseHas('credit_transactions', [
            'wallet_id' => $wallet->id,
            'amount_credits' => 100,
            'type' => 'purchase',
            'related_type' => 'credit_purchase',
            'related_id' => $purchase->id,
        ]);
    }
}
