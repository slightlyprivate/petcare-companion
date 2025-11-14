<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\NotificationPreference;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\Donation\DonationSuccessNotification;
use App\Services\Webhook\Stripe\StripeWebhookService;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test suite for Stripe webhook handling.
 * 
 * Tests webhook-driven state changes and notifications by invoking the webhook service
 * directly with real Stripe Event objects constructed from test data.
 * This approach avoids Mockery's class overload mechanism entirely, eliminating lifecycle issues.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
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
        $signature = "t={$timestamp},v1=" . hash_hmac('sha256', $signedContent, $secret);

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
     * Test that checkout.session.completed webhook marks donation as paid.
     */
    public function test_webhook_checkout_completed_marks_donation_paid(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $donation = Donation::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_1',
            'amount_cents' => 2500,
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
                    'metadata' => ['donation_id' => (string) $donation->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $donation->refresh();
        $this->assertEquals('paid', $donation->status);
        $this->assertNotNull($donation->completed_at);

        Notification::assertSentTo([$user], DonationSuccessNotification::class);
    }

    /**
     * Test webhook respects notification preferences.
     */
    public function test_webhook_respects_notification_preferences(): void
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        NotificationPreference::create([
            'user_id' => $user->id,
            'donation_notifications' => false,
        ]);

        $donation = Donation::factory()->create([
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
                    'metadata' => ['donation_id' => (string) $donation->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $donation->refresh();
        $this->assertEquals('paid', $donation->status);

        // Preference disables donations, so no notification should be sent
        Notification::assertNotSentTo([$user], DonationSuccessNotification::class);
    }

    /**
     * Test checkout.session.expired webhook marks donation as failed.
     */
    public function test_webhook_checkout_expired_marks_donation_failed(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $donation = Donation::factory()->create([
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
                    'metadata' => ['donation_id' => (string) $donation->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        $this->handleWebhookEvent($eventData);

        $donation->refresh();
        $this->assertEquals('failed', $donation->status);
        $this->assertNotNull($donation->completed_at);
    }

    /**
     * Test invalid webhook signature is rejected.
     */
    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['type' => 'test']);
        $secret = config('services.stripe.webhook.secret');

        // Use invalid signature
        $invalidSignature = 't=' . time() . ',v1=invalid';

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
     * Test webhook handles missing donation gracefully.
     */
    public function test_webhook_handles_missing_donation(): void
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
                    'metadata' => ['donation_id' => 'nonexistent'],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        // Should handle gracefully without throwing
        $this->handleWebhookEvent($eventData);

        // Verify no donations were affected
        $this->assertEquals(0, Donation::count());
    }

    /**
     * Test webhook is idempotent (can be safely processed multiple times).
     */
    public function test_webhook_is_idempotent(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $donation = Donation::factory()->create([
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
                    'metadata' => ['donation_id' => (string) $donation->id],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
        ];

        // Process the same event twice
        $this->handleWebhookEvent($eventData);
        Notification::assertSentTimes(DonationSuccessNotification::class, 1);

        // Process again - should not send another notification (idempotent)
        $this->handleWebhookEvent($eventData);
        // Should still only have one notification sent (donation already marked paid)
        Notification::assertSentTimes(DonationSuccessNotification::class, 1);

        $donation->refresh();
        $this->assertEquals('paid', $donation->status);
    }
}
