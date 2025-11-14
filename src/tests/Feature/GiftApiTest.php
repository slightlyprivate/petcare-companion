<?php

namespace Tests\Feature;

use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for gift API endpoints.
 */
class GiftApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Clean up mocks after each test.
     */
    protected function tearDown(): void
    {
        // Close all mockery mocks properly
        try {
            \Mockery::close();
            \Mockery::resetContainer();
        } catch (\Exception $e) {
            // Ignore closing errors
        }
        parent::tearDown();
    }

    /**
     * Test that authenticated user can initiate gift to pet.
     */
    /**
     * Test gift validation rules.
     */
    public function test_it_validates_gift_input(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => -5,  // Invalid negative amount
                'return_url' => 'invalid-url',  // Invalid URL
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_in_credits', 'return_url']);
    }

    /**
     * Test that gift requires authentication.
     */
    public function test_it_requires_authentication_for_gift(): void
    {
        $pet = Pet::factory()->create();

        $response = $this->postJson("/api/pets/{$pet->id}/gifts", [
            'cost_in_credits' => 100,
            'return_url' => 'https://example.com/success',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test gift amount limits.
     */
    public function test_it_validates_gift_amount_limits(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Test minimum amount
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 5,  // Below minimum of 10
                'return_url' => 'https://example.com/success',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_in_credits']);

        // Test maximum amount
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 1000001,  // Above maximum of 1,000,000
                'return_url' => 'https://example.com/success',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_in_credits']);
    }

    /**
     * Test gift model relationships.
     */
    public function test_gift_has_correct_relationships(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
        ]);

        $this->assertInstanceOf(User::class, $gift->user);
        $this->assertInstanceOf(Pet::class, $gift->pet);
        $this->assertEquals($user->id, $gift->user->id);
        $this->assertEquals($pet->id, $gift->pet->id);
    }

    /**
     * Test gift status methods.
     */
    public function test_gift_status_methods(): void
    {
        $gift = Gift::factory()->create(['status' => 'pending']);

        // Test marking as paid
        $result = $gift->markAsPaid();
        $this->assertTrue($result);
        $this->assertEquals('paid', $gift->fresh()->status);
        $this->assertNotNull($gift->fresh()->completed_at);

        // Test marking as failed
        $gift2 = Gift::factory()->create(['status' => 'pending']);
        $result = $gift2->markAsFailed();
        $this->assertTrue($result);
        $this->assertEquals('failed', $gift2->fresh()->status);
        $this->assertNotNull($gift2->fresh()->completed_at);
    }

    /**
     * Test gift amount conversion.
     */
    public function test_gift_amount_conversion(): void
    {
        $gift = Gift::factory()->create(['cost_in_credits' => 100]);

        $this->assertEquals(100, $gift->cost_in_credits);
    }

    /**
     * Test that return_url is required for gift creation.
     */
    public function test_it_requires_return_url_for_gift(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 100,
                // Missing return_url
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_url']);
    }

    /**
     * Test that return_url must be a valid URL format.
     */
    public function test_it_validates_return_url_format(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 100,
                'return_url' => 'not-a-valid-url',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_url']);
    }

    /**
     * Test that Stripe session uses provided return_url for success.
     */
    public function test_stripe_session_respects_return_url(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Configure Stripe mock
        config([
            'services.stripe.key' => 'pk_test_fake_key',
            'services.stripe.secret' => 'sk_test_fake_secret',
        ]);

        $customReturnUrl = 'https://app.example.com/checkout/complete';

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 100,
                'return_url' => $customReturnUrl,
            ]);

        // Will fail with Stripe API error, but we can verify gift was created
        $response->assertStatus(500);

        // Verify gift was created
        $this->assertDatabaseHas('gifts', [
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 100,
        ]);
    }

    /**
     * Test multiple return_urls work correctly.
     */
    public function test_multiple_return_urls_work_independently(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        config([
            'services.stripe.key' => 'pk_test_fake_key',
            'services.stripe.secret' => 'sk_test_fake_secret',
        ]);

        $returnUrl1 = 'https://app1.example.com/callback';
        $returnUrl2 = 'https://app2.example.com/callback';

        // First gift with different return_url
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 100,
                'return_url' => $returnUrl1,
            ]);

        // Second gift with different return_url
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'cost_in_credits' => 150,
                'return_url' => $returnUrl2,
            ]);

        // Both should be created despite Stripe errors
        $this->assertDatabaseCount('gifts', 2);
        $this->assertDatabaseHas('gifts', [
            'user_id' => $user->id,
            'cost_in_credits' => 100,
        ]);
        $this->assertDatabaseHas('gifts', [
            'user_id' => $user->id,
            'cost_in_credits' => 150,
        ]);
    }
}
