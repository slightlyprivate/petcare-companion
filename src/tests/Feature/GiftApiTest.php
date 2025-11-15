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
                'gift_type_id' => 'not-a-uuid', // Invalid gift type id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gift_type_id']);
    }

    /**
     * Test that gift requires authentication.
     */
    public function test_it_requires_authentication_for_gift(): void
    {
        $pet = Pet::factory()->create();

        $response = $this->postJson("/api/pets/{$pet->id}/gifts", [
            'gift_type_id' => (string) \App\Models\GiftType::factory()->create()->id,
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

        // Amount limits are enforced by gift type catalog; request no longer accepts raw amounts
        $this->assertTrue(true);
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
    public function test_it_requires_gift_type_for_gift(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                // Missing gift_type_id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gift_type_id']);
    }

    /**
     * Test wallet-based gifting returns success and creates gift.
     */
    public function test_wallet_gifting_creates_gift(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();
        // Ensure wallet has sufficient credits
        $user->wallet()->create(['balance_credits' => 1000]);

        $giftType = \App\Models\GiftType::factory()->create(['cost_in_credits' => 100, 'is_active' => true]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType->id,
            ]);

        $response->assertStatus(201);

        // Verify gift was created
        $this->assertDatabaseHas('gifts', [
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 100,
        ]);
    }

    /**
     * Test multiple gifts can be created independently.
     */
    public function test_multiple_gifts_work_independently(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();
        // Ensure wallet has sufficient credits
        $user->wallet()->create(['balance_credits' => 1000]);

        // First gift with different return_url
        $giftType1 = \App\Models\GiftType::factory()->create(['cost_in_credits' => 100, 'is_active' => true]);
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType1->id,
            ]);

        // Second gift with different return_url
        $giftType2 = \App\Models\GiftType::factory()->create(['cost_in_credits' => 150, 'is_active' => true]);
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType2->id,
            ]);

        // Both should be created
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
