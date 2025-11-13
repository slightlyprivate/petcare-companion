<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for donation receipt and user data compliance endpoints.
 */
class DonationReceiptAndUserDataComplianceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can export donation receipt.
     */
    public function test_it_can_export_donation_receipt(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create a completed donation
        $donation = Donation::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
            'stripe_session_id' => 'cs_test_123',
            'stripe_charge_id' => 'ch_test_123',
            'stripe_metadata' => [
                'amount' => 2500,
                'currency' => 'usd',
                'payment_method' => 'card',
                'brand' => 'visa',
                'last4' => '4242',
            ],
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/donations/{$donation->id}/receipt");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="receipt_'.$donation->id.'.pdf"');

        // Verify receipt is a valid PDF (starts with PDF header)
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    /**
     * Test that user cannot export another user's donation receipt.
     */
    public function test_it_prevents_unauthorized_receipt_export(): void
    {
        /** @var Authenticatable $user1 */
        $user1 = User::factory()->create();
        /** @var Authenticatable $user2 */
        $user2 = User::factory()->create();
        $pet = Pet::factory()->create();

        $donation = Donation::factory()->create([
            'user_id' => $user1->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user2, 'sanctum')
            ->getJson("/api/donations/{$donation->id}/receipt");

        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated user cannot export receipt.
     */
    public function test_it_requires_authentication_for_receipt_export(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $donation = Donation::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
        ]);

        $response = $this->getJson("/api/donations/{$donation->id}/receipt");

        $response->assertStatus(401);
    }

    /**
     * Test that user can request data export.
     */
    public function test_it_can_request_user_data_export(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/data/export');

        $response->assertStatus(202)
            ->assertJson([
                'status' => 'processing',
            ])
            ->assertJsonStructure([
                'message',
                'status',
            ]);
    }

    /**
     * Test that unauthenticated user cannot request data export.
     */
    public function test_it_requires_authentication_for_data_export(): void
    {
        $response = $this->getJson('/api/user/data/export');

        $response->assertStatus(401);
    }

    /**
     * Test that user can request account deletion.
     */
    public function test_it_can_request_user_data_deletion(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/user/data/delete');

        $response->assertStatus(202)
            ->assertJson([
                'status' => 'processing',
            ])
            ->assertJsonStructure([
                'message',
                'status',
            ]);
    }

    /**
     * Test that unauthenticated user cannot request data deletion.
     */
    public function test_it_requires_authentication_for_data_deletion(): void
    {
        $response = $this->deleteJson('/api/user/data/delete');

        $response->assertStatus(401);
    }
}
