<?php

namespace Tests\Feature;

use App\Mail\Auth\UserDataDeletionInitiated;
use App\Mail\Auth\UserDataDeletionNotification;
use App\Models\Appointment;
use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
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
     * Test that user can request account deletion at /api/user/data endpoint.
     */
    public function test_it_can_request_user_data_deletion(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/user/data');

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
        $response = $this->deleteJson('/api/user/data');

        $response->assertStatus(401);
    }

    /**
     * Test that user data is hard deleted when deletion is requested.
     */
    public function test_it_hard_deletes_user_data(): void
    {
        Mail::fake();
        Queue::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id]);
        Appointment::factory()->create(['pet_id' => $pet->id]);
        Donation::factory()->create(['user_id' => $user->id]);

        $userId = $user->id;
        $userEmail = $user->email;

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/user/data');

        // Process queued jobs
        Queue::assertPushed(\App\Jobs\DeleteUserDataJob::class);
    }

    /**
     * Test that user data deletion actually performs hard delete operation.
     */
    public function test_user_data_deletion_job_hard_deletes_data(): void
    {
        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id]);
        $appointment = Appointment::factory()->create(['pet_id' => $pet->id]);
        Donation::factory()->create(['user_id' => $user->id]);

        $userId = $user->id;
        $userEmail = $user->email;

        // Dispatch job directly to bypass queue
        (new \App\Jobs\DeleteUserDataJob($user))->handle();

        // Verify user is hard deleted (not soft deleted)
        $this->assertNull(User::find($userId));
        $this->assertDatabaseMissing('users', ['id' => $userId]);

        // Verify related data is hard deleted
        $this->assertDatabaseMissing('pets', ['user_id' => $userId]);
        $this->assertDatabaseMissing('donations', ['user_id' => $userId]);
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);

        // Verify deletion emails were sent
        Mail::assertSent(UserDataDeletionInitiated::class);
        Mail::assertSent(UserDataDeletionNotification::class);
    }

    /**
     * Test that user data deletion sends confirmation emails.
     */
    public function test_it_sends_deletion_confirmation_emails(): void
    {
        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $userEmail = $user->email;

        // Dispatch job directly to bypass queue
        (new \App\Jobs\DeleteUserDataJob($user))->handle();

        Mail::assertSent(UserDataDeletionInitiated::class);
        Mail::assertSent(UserDataDeletionNotification::class);
    }
}
