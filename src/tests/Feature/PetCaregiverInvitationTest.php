<?php

namespace Tests\Feature;

use App\Mail\PetCaregiverInvitationMail;
use App\Models\Pet;
use App\Models\PetCaregiverInvitation;
use App\Models\PetUser;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the Pet Caregiver Invitation API endpoints.
 */
class PetCaregiverInvitationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function owner_can_send_caregiver_invitation()
    {
        Mail::fake();

        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $caregiverEmail = 'caregiver@test.localhost';

        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => $caregiverEmail,
        ]);

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->where('data.pet_id', $pet->id)
                    ->where('data.invitee_email', $caregiverEmail)
                    ->where('data.status', 'pending')
                    ->has('data.expires_at')
                    ->etc()
            );

        $this->assertDatabaseHas('pet_caregiver_invitations', [
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiverEmail,
            'status' => 'pending',
        ]);

        Mail::assertQueued(PetCaregiverInvitationMail::class, function ($mail) use ($caregiverEmail) {
            return $mail->hasTo($caregiverEmail);
        });

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'default',
            'description' => 'caregiver_invitation_sent',
            'subject_type' => PetCaregiverInvitation::class,
            'causer_type' => User::class,
            'causer_id' => $owner->id,
        ]);
    }

    #[Test]
    public function non_owner_cannot_send_caregiver_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $nonOwner */
        $nonOwner = User::factory()->create(['email' => 'nonowner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $response = $this->actingAs($nonOwner, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => 'caregiver@test.localhost',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function caregiver_can_send_invitation_if_they_are_an_owner_role()
    {
        Mail::fake();

        /** @var Authenticatable $originalOwner */
        $originalOwner = User::factory()->create(['email' => 'original@test.localhost']);
        /** @var Authenticatable $caregiverWithOwnerRole */
        $caregiverWithOwnerRole = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($originalOwner)->create();

        // Add caregiver with owner role
        PetUser::create([
            'pet_id' => $pet->id,
            'user_id' => $caregiverWithOwnerRole->id,
            'role' => 'owner',
        ]);

        $response = $this->actingAs($caregiverWithOwnerRole, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => 'newcaregiver@test.localhost',
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function caregiver_role_cannot_send_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->id,
            'user_id' => $caregiver->id,
            'role' => 'caregiver',
        ]);

        $response = $this->actingAs($caregiver, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => 'another@test.localhost',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function invitation_validation_rejects_invalid_email()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create();
        $pet = Pet::factory()->for($owner)->create();

        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('invitee_email');
    }

    #[Test]
    public function invitation_validation_prevents_self_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create();
        $pet = Pet::factory()->for($owner)->create();

        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => $owner->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('invitee_email');
    }

    #[Test]
    public function invitation_validation_prevents_duplicate_pending_invitation()
    {
        Mail::fake();

        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();
        $caregiverEmail = 'caregiver@test.localhost';

        // Create first invitation (should succeed)
        $first = $this->actingAs($owner, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => $caregiverEmail,
        ]);
        $first->assertStatus(201);

        // Attempt duplicate (should be rejected)
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/pets/{$pet->id}/caregiver-invitations", [
            'invitee_email' => $caregiverEmail,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('invitee_email');
    }

    #[Test]
    public function user_can_accept_valid_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiver->email,
        ]);

        $response = $this->actingAs($caregiver, 'sanctum')->postJson("/api/caregiver-invitations/{$invitation->token}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Invitation accepted successfully. You are now a caregiver for this pet.',
            ]);

        $this->assertDatabaseHas('pet_caregiver_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
            'invitee_id' => $caregiver->id,
        ]);

        $this->assertDatabaseHas('pet_user', [
            'pet_id' => $pet->id,
            'user_id' => $caregiver->id,
            'role' => 'caregiver',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'default',
            'description' => 'caregiver_invitation_accepted',
            'subject_type' => PetCaregiverInvitation::class,
            'subject_id' => $invitation->id,
            'causer_type' => User::class,
            'causer_id' => $caregiver->id,
        ]);
    }

    #[Test]
    public function cannot_accept_invitation_with_wrong_email()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $wrongUser */
        $wrongUser = User::factory()->create(['email' => 'wrong@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'correct@test.localhost',
        ]);

        $response = $this->actingAs($wrongUser, 'sanctum')->postJson("/api/caregiver-invitations/{$invitation->token}/accept");

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_accept_expired_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiver->email,
        ]);

        // Manually expire the invitation
        $invitation->update(['expires_at' => now()->subDay()]);

        $response = $this->actingAs($caregiver, 'sanctum')->postJson("/api/caregiver-invitations/{$invitation->token}/accept");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This invitation has expired.',
            ]);
    }

    #[Test]
    public function cannot_accept_already_accepted_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiver->email,
        ]);

        $invitation->markAsAccepted($caregiver->id);

        $response = $this->actingAs($caregiver, 'sanctum')->postJson("/api/caregiver-invitations/{$invitation->token}/accept");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This invitation has already been accepted.',
            ]);
    }

    #[Test]
    public function cannot_accept_revoked_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiver->email,
        ]);

        $invitation->markAsRevoked();

        $response = $this->actingAs($caregiver, 'sanctum')->postJson("/api/caregiver-invitations/{$invitation->token}/accept");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This invitation has already been revoked.',
            ]);
    }

    #[Test]
    public function user_can_list_sent_and_received_invitations()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet1 = Pet::factory()->for($owner)->create(['name' => 'Buddy']);
        $pet2 = Pet::factory()->for($owner)->create(['name' => 'Max']);

        // Owner sends invitation to caregiver
        $sentInvitation = PetCaregiverInvitation::create([
            'pet_id' => $pet1->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiver->email,
        ]);

        // Another user invites the owner
        $otherUser = User::factory()->create(['email' => 'other@test.localhost']);
        $otherPet = Pet::factory()->for($otherUser)->create(['name' => 'Luna']);
        $receivedInvitation = PetCaregiverInvitation::create([
            'pet_id' => $otherPet->id,
            'inviter_id' => $otherUser->id,
            'invitee_email' => $owner->email,
        ]);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/caregiver-invitations');

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has('data.sent', 1)
                    ->has('data.received', 1)
                    ->where('data.sent.0.id', $sentInvitation->id)
                    ->where('data.sent.0.pet.name', 'Buddy')
                    ->where('data.received.0.id', $receivedInvitation->id)
                    ->where('data.received.0.pet.name', 'Luna')
                    ->has('data.received.0.token') // Token visible for pending received invitations
            );
    }

    #[Test]
    public function inviter_can_revoke_pending_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'caregiver@test.localhost',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/caregiver-invitations/{$invitation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Invitation revoked successfully.',
            ]);

        $this->assertDatabaseHas('pet_caregiver_invitations', [
            'id' => $invitation->id,
            'status' => 'revoked',
        ]);
    }

    #[Test]
    public function inviter_can_revoke_accepted_invitation_and_remove_access()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $caregiver */
        $caregiver = User::factory()->create(['email' => 'caregiver@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => $caregiver->email,
        ]);

        $invitation->markAsAccepted($caregiver->id);

        PetUser::create([
            'pet_id' => $pet->id,
            'user_id' => $caregiver->id,
            'role' => 'caregiver',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/caregiver-invitations/{$invitation->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('pet_caregiver_invitations', [
            'id' => $invitation->id,
            'status' => 'revoked',
        ]);

        $this->assertDatabaseMissing('pet_user', [
            'pet_id' => $pet->id,
            'user_id' => $caregiver->id,
        ]);
    }

    #[Test]
    public function non_inviter_cannot_revoke_invitation()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        /** @var Authenticatable $otherUser */
        $otherUser = User::factory()->create(['email' => 'other@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'caregiver@test.localhost',
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')->deleteJson("/api/caregiver-invitations/{$invitation->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_invitation_endpoints()
    {
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'test@test.localhost',
        ]);

        $this->postJson("/api/pets/{$pet->id}/caregiver-invitations", ['invitee_email' => 'test@test.localhost'])
            ->assertStatus(401);

        $this->postJson("/api/caregiver-invitations/{$invitation->token}/accept")
            ->assertStatus(401);

        $this->getJson('/api/caregiver-invitations')
            ->assertStatus(401);

        $this->deleteJson("/api/caregiver-invitations/{$invitation->id}")
            ->assertStatus(401);
    }

    #[Test]
    public function invitation_token_is_unique_and_secure()
    {
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation1 = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'user1@test.localhost',
        ]);

        $invitation2 = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'user2@test.localhost',
        ]);

        $this->assertNotEquals($invitation1->token, $invitation2->token);
        $this->assertTrue(strlen($invitation1->token) >= 40);
        $this->assertTrue(strlen($invitation2->token) >= 40);
    }

    #[Test]
    public function invitation_auto_sets_expiration_date()
    {
        $owner = User::factory()->create(['email' => 'owner@test.localhost']);
        $pet = Pet::factory()->for($owner)->create();

        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'test@test.localhost',
        ]);

        $this->assertNotNull($invitation->expires_at);
        $this->assertTrue($invitation->expires_at->isFuture());
        $this->assertGreaterThanOrEqual(6, now()->diffInDays($invitation->expires_at));
        $this->assertLessThanOrEqual(8, now()->diffInDays($invitation->expires_at));
    }
}
