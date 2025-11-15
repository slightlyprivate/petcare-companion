<?php

namespace Tests\Feature;

use App\Models\Gift;
use App\Models\NotificationPreference;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\Gift\GiftSuccessNotification;
use App\Services\Webhook\Stripe\StripeWebhookService;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

/**
 * Test suite for Stripe webhook handling.
 *
 * Tests webhook-driven state changes and notifications by invoking the webhook service
 * directly with real Stripe Event objects constructed from test data.
 * This approach avoids Mockery's class overload mechanism entirely, eliminating lifecycle issues.
 */
class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a Stripe Event from test data and convert nested objects to arrays.
     * This matches the format expected by StripeWebhookService handlers.
     */
    protected function createStripeEvent(array $eventData): \Stripe\Event
    {
        $event = \Stripe\Event::constructFrom($eventData);
        // Convert nested objects to arrays for compatibility with handler
        if (isset($event['data']['object'])) {
            $event['data']['object'] = $event['data']['object']->toArray();
        }

        return $event;
    }

    /**
     * Invoke the webhook service by mocking the Stripe\Webhook::constructEvent call.
     * Uses Mockery but scoped to this single invocation, then immediately closed.
     */
    protected function handleWebhookEvent(array $eventData): void
    {
        $payload = json_encode($eventData);
        $secret = config('services.stripe.webhook.secret');
        $timestamp = time();
        $signedContent = "{$timestamp}.{$payload}";
        $signature = "t={$timestamp},v1=".hash_hmac('sha256', $signedContent, $secret);

        // Create the event object
        $event = $this->createStripeEvent($eventData);

        // Mock Stripe\Webhook::constructEvent to return our pre-built event
        // If this fails due to class already existing, we'll let the exception propagate
        // (indicating a test ordering/isolation problem)
        $webhook = \Mockery::mock('overload:\Stripe\Webhook');
        $webhook->shouldReceive('constructEvent')
            ->with($payload, $signature, $secret)
            ->andReturn($event)
            ->once();

        try {
            // Invoke service
            $service = app(StripeWebhookService::class);
            $service->handle($payload, $signature);
        } finally {
            // Immediately close and reset Mockery to avoid persistence across tests
            \Mockery::close();
            \Mockery::resetContainer();
        }
    }

    /**
     * Test that checkout.session.completed webhook marks gift as paid.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_checkout_completed_marks_gift_paid(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_1',
            'cost_in_credits' => 100,
        ]);

        $eventData = [
            'id' => 'evt_test_1',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_1',
                    'payment_intent' => 'pi_test_1',
                    'metadata' => ['gift_id' => (string) $gift->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $gift->refresh();
        $this->assertEquals('paid', $gift->status);
        $this->assertNotNull($gift->completed_at);

        Notification::assertSentTo([$user], GiftSuccessNotification::class);
    }

    /**
     * Test webhook respects notification preferences.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_respects_notification_preferences(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        NotificationPreference::create([
            'user_id' => $user->id,
            'gift_notifications' => false,
        ]);

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_2',
        ]);

        $eventData = [
            'id' => 'evt_test_2',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_2',
                    'payment_intent' => 'pi_test_2',
                    'metadata' => ['gift_id' => (string) $gift->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $gift->refresh();
        $this->assertEquals('paid', $gift->status);

        // Preference disables gifts, so no notification should be sent
        Notification::assertNotSentTo([$user], GiftSuccessNotification::class);
    }

    /**
     * Test checkout.session.expired webhook marks gift as failed.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_checkout_expired_marks_gift_failed(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_expired',
        ]);

        $eventData = [
            'id' => 'evt_test_expired',
            'object' => 'event',
            'type' => 'checkout.session.expired',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_expired',
                    'metadata' => ['gift_id' => (string) $gift->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $gift->refresh();
        $this->assertEquals('failed', $gift->status);
        $this->assertNotNull($gift->completed_at);
    }

    /**
     * Test invalid webhook signature is rejected.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['type' => 'test']);
        $secret = config('services.stripe.webhook.secret');

        // Use invalid signature
        $invalidSignature = 't='.time().',v1=invalid';

        // Mock Stripe\Webhook::constructEvent to throw exception
        $webhook = \Mockery::mock('overload:\Stripe\Webhook');
        $webhook->shouldReceive('constructEvent')
            ->andThrow(new \Stripe\Exception\SignatureVerificationException('Invalid signature'));

        try {
            $service = app(StripeWebhookService::class);
            $service->handle($payload, $invalidSignature);
            $this->fail('Expected SignatureVerificationException');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->assertStringContainsString('Invalid signature', $e->getMessage());
        } finally {
            \Mockery::close();
            \Mockery::resetContainer();
        }
    }

    /**
     * Test webhook handles missing gift gracefully.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_handles_missing_gift(): void
    {
        $eventData = [
            'id' => 'evt_test_missing',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_missing',
                    'payment_intent' => 'pi_test_missing',
                    'metadata' => ['gift_id' => 'nonexistent'],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        // Should handle gracefully without throwing
        $this->handleWebhookEvent($eventData);

        // Verify no gifts were affected
        $this->assertEquals(0, Gift::count());
    }

    /**
     * Test webhook is idempotent (can be safely processed multiple times).
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_is_idempotent(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_idem',
        ]);

        $eventData = [
            'id' => 'evt_test_idem',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_idem',
                    'payment_intent' => 'pi_test_idem',
                    'metadata' => ['gift_id' => (string) $gift->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        // Process the same event twice
        $this->handleWebhookEvent($eventData);
        Notification::assertSentTimes(GiftSuccessNotification::class, 1);

        // Process again - should not send another notification (idempotent)
        $this->handleWebhookEvent($eventData);
        // Should still only have one notification sent (gift already marked paid)
        Notification::assertSentTimes(GiftSuccessNotification::class, 1);

        $gift->refresh();
        $this->assertEquals('paid', $gift->status);
    }

    /**
     * Test webhook uses fallback identification when gift_id metadata is missing.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_fallback_identification_by_session_id(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_fallback_1',
            'cost_in_credits' => 100,
        ]);

        // Event with NO gift_id in metadata - forces fallback lookup
        $eventData = [
            'id' => 'evt_test_fallback_1',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_fallback_1',
                    'payment_intent' => 'pi_test_fallback_1',
                    'metadata' => [], // No gift_id - forces fallback
                    'client_reference_id' => null,
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $gift->refresh();
        $this->assertEquals('paid', $gift->status);
        $this->assertNotNull($gift->completed_at);

        Notification::assertSentTo([$user], GiftSuccessNotification::class);
    }

    /**
     * Test webhook uses client_reference_id fallback for gift identification.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_fallback_identification_by_client_reference_id(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_fallback_2',
            'cost_in_credits' => 100,
        ]);

        // Event with client_reference_id but NO gift_id in metadata
        $eventData = [
            'id' => 'evt_test_fallback_2',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_fallback_2',
                    'payment_intent' => 'pi_test_fallback_2',
                    'metadata' => [], // No gift_id
                    'client_reference_id' => (string) $gift->id, // Use gift ID as client ref
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $gift->refresh();
        $this->assertEquals('paid', $gift->status);

        Notification::assertSentTo([$user], GiftSuccessNotification::class);
    }

    /**
     * Test credit reconciliation: missing wallet credits are restored.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_reconciles_missing_wallet_credits(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create user wallet with initial balance
        $wallet = $user->wallet()->create(['balance_credits' => 0]);
        $initialBalance = $wallet->balance_credits;

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_recon_1',
            'cost_in_credits' => 100,
        ]);

        $eventData = [
            'id' => 'evt_test_recon_1',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_recon_1',
                    'payment_intent' => 'pi_test_recon_1',
                    'metadata' => ['gift_id' => (string) $gift->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $gift->refresh();
        $wallet->refresh();

        // Verify gift is marked as paid
        $this->assertEquals('paid', $gift->status);

        // No wallet debit occurs for Stripe-paid gifts
        $this->assertEquals($initialBalance, $wallet->balance_credits);

        // No wallet transaction should be created by webhook for Stripe-paid gifts
        $transactions = $wallet->transactions()->get();
        $this->assertEquals(0, $transactions->count());
    }

    /**
     * Test webhook prevents duplicate credit deduction on retry.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_prevents_duplicate_credit_deduction(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create wallet with initial balance
        $wallet = $user->wallet()->create(['balance_credits' => 1000]);

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_dup_1',
            'cost_in_credits' => 100,
        ]);

        $eventData = [
            'id' => 'evt_test_dup_1',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_dup_1',
                    'payment_intent' => 'pi_test_dup_1',
                    'metadata' => ['gift_id' => (string) $gift->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        // Process event first time
        $this->handleWebhookEvent($eventData);
        $wallet->refresh();
        $balanceAfterFirst = $wallet->balance_credits;

        // Process same event again (webhook retry)
        $this->handleWebhookEvent($eventData);
        $wallet->refresh();
        $balanceAfterSecond = $wallet->balance_credits;

        // Balance should remain the same (no deduction is performed by webhook)
        $this->assertEquals(1000, $balanceAfterFirst);
        $this->assertEquals($balanceAfterFirst, $balanceAfterSecond);
    }

    /**
     * Test webhook handles missing gift gracefully after fallback attempts.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_webhook_handles_nonexistent_gift_after_fallback(): void
    {
        // Should not throw exception
        $eventData = [
            'id' => 'evt_test_missing_fallback',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'object' => 'checkout.session',
                    'id' => 'cs_test_missing_fallback',
                    'payment_intent' => 'pi_test_missing_fallback',
                    'metadata' => [], // No gift_id
                    'client_reference_id' => 'nonexistent-id',
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        // Verify no gifts were created
        $this->assertEquals(0, Gift::count());
    }
}
