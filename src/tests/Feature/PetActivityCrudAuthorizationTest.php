<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\PetUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetActivityCrudAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_activity_for_their_pet(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        // Associate owner via pivot
        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        $payload = [
            'type' => 'feeding',
            'description' => 'Morning kibble',
            'media_url' => null,
        ];

        $response = $this->actingAs($owner)->postJson('/api/pets/' . $pet->getKey() . '/activities', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);

        $this->assertDatabaseHas('pet_activities', [
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'feeding',
        ]);
    }

    public function test_caregiver_can_create_activity_for_assigned_pet(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $caregiver */
        $caregiver = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        // Associate caregiver via pivot
        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'role' => 'caregiver',
        ]);

        $payload = [
            'type' => 'walk',
            'description' => 'Afternoon walk around the block',
            'media_url' => 'https://example.com/walk.jpg',
        ];

        $response = $this->actingAs($caregiver)->postJson('/api/pets/' . $pet->getKey() . '/activities', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);

        $this->assertDatabaseHas('pet_activities', [
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'type' => 'walk',
        ]);
    }

    public function test_unauthorized_user_cannot_create_activity(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $stranger */
        $stranger = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        // No pivot association for stranger

        $payload = [
            'type' => 'feeding',
            'description' => 'Unauthorized attempt',
        ];

        $response = $this->actingAs($stranger)->postJson('/api/pets/' . $pet->getKey() . '/activities', $payload);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('pet_activities', [
            'pet_id' => $pet->getKey(),
            'user_id' => $stranger->getKey(),
        ]);
    }

    public function test_owner_can_delete_their_pet_activity(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        // Associate owner via pivot
        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        $activity = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'grooming',
            'description' => 'Bath time',
        ]);

        $response = $this->actingAs($owner)->deleteJson('/api/activities/' . $activity->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('pet_activities', [
            'id' => $activity->getKey(),
        ]);
    }

    public function test_caregiver_cannot_delete_activity(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $caregiver */
        $caregiver = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        // Associate owner and caregiver via pivot
        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'role' => 'caregiver',
        ]);

        $activity = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'type' => 'walk',
            'description' => 'Evening walk',
        ]);

        $response = $this->actingAs($caregiver)->deleteJson('/api/activities/' . $activity->getKey());

        $response->assertStatus(403);

        $this->assertDatabaseHas('pet_activities', [
            'id' => $activity->getKey(),
        ]);
    }

    public function test_unauthorized_user_cannot_delete_activity(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $stranger */
        $stranger = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        $activity = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'feeding',
            'description' => 'Dinner',
        ]);

        $response = $this->actingAs($stranger)->deleteJson('/api/activities/' . $activity->getKey());

        $response->assertStatus(403);

        $this->assertDatabaseHas('pet_activities', [
            'id' => $activity->getKey(),
        ]);
    }

    public function test_listing_activities_requires_authentication(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        $response = $this->getJson('/api/pets/' . $pet->getKey() . '/activities');

        $response->assertStatus(401);
    }

    public function test_creating_activity_requires_authentication(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        $payload = [
            'type' => 'feeding',
            'description' => 'Unauthorized',
        ];

        $response = $this->postJson('/api/pets/' . $pet->getKey() . '/activities', $payload);

        $response->assertStatus(401);
    }

    public function test_deleting_activity_requires_authentication(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        $activity = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'type' => 'feeding',
            'description' => 'Dinner',
        ]);

        $response = $this->deleteJson('/api/activities/' . $activity->getKey());

        $response->assertStatus(401);
    }
}
