<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test suite for rate limiting and abuse protection.
 */
class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_throttles_pet_creation()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Configure Stripe for testing
        config([
            'services.stripe.key' => 'pk_test_fake_key',
            'services.stripe.secret' => 'sk_test_fake_secret',
        ]);

        // Make 20 requests per hour (should succeed)
        for ($i = 0; $i < 20; $i++) {
            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/pets', [
                    'name' => 'Pet '.$i,
                    'species' => 'dog',
                    'owner_name' => 'Owner '.$i,
                ]);

            // All should succeed (might fail due to validation, but not rate limit)
            $this->assertNotEquals(429, $response->status());
        }

        // 21st request in same hour should be throttled
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/pets', [
                'name' => 'Pet 21',
                'species' => 'dog',
                'owner_name' => 'Owner 21',
            ]);

        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function it_throttles_gift_creation()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Configure Stripe for testing
        config([
            'services.stripe.key' => 'pk_test_fake_key',
            'services.stripe.secret' => 'sk_test_fake_secret',
        ]);

        // Make 5 requests within an hour (should succeed or fail due to validation)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($user, 'sanctum')
                ->postJson("/api/pets/{$pet->id}/gifts", [
                    'cost_in_credits' => 100,
                    'gift_type_id' => (string) \App\Models\GiftType::factory()->create()->id,
                ]);

            // All should not be 429 (rate limit)
            $this->assertNotEquals(429, $response->status());
        }

        // 6th request in same hour should be throttled
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 100,
                'gift_type_id' => (string) \App\Models\GiftType::factory()->create()->id,
            ]);

        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function it_throttles_stripe_webhook()
    {
        // Configure Stripe for testing
        config([
            'services.stripe.key' => 'pk_test_fake_key',
            'services.stripe.secret' => 'sk_test_fake_secret',
            'services.stripe.webhook.secret' => 'sk_test_webhook_secret',
        ]);

        // Make 100 requests (should succeed)
        for ($i = 0; $i < 100; $i++) {
            $response = $this->postJson('/api/webhooks/stripe', [
                'type' => 'charge.succeeded',
                'data' => ['object' => []],
            ], [
                'Stripe-Signature' => 'invalid_signature',
            ]);

            // All should not be 429 (rate limit)
            // They'll fail webhook validation but not be rate limited
            $this->assertNotEquals(429, $response->status());
        }

        // 101st request in same minute should be throttled
        $response = $this->postJson('/api/webhooks/stripe', [
            'type' => 'charge.succeeded',
            'data' => ['object' => []],
        ], [
            'Stripe-Signature' => 'invalid_signature',
        ]);

        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function it_applies_per_user_rate_limiting_for_pet_creation()
    {
        /** @var Authenticatable $user1 */
        $user1 = User::factory()->create();
        /** @var Authenticatable $user2 */
        $user2 = User::factory()->create();

        // Configure Stripe for testing
        config([
            'services.stripe.key' => 'pk_test_fake_key',
            'services.stripe.secret' => 'sk_test_fake_secret',
        ]);

        // User 1 makes 10 requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user1, 'sanctum')
                ->postJson('/api/pets', [
                    'name' => 'Pet '.$i,
                    'species' => 'dog',
                    'owner_name' => 'Owner '.$i,
                ]);

            $this->assertNotEquals(429, $response->status());
        }

        // User 2 should still be able to create pets (separate rate limit)
        $response = $this->actingAs($user2, 'sanctum')
            ->postJson('/api/pets', [
                'name' => 'Pet User2',
                'species' => 'dog',
                'owner_name' => 'Owner User2',
            ]);

        // User 2 should not be rate limited
        $this->assertNotEquals(429, $response->status());
    }
}
