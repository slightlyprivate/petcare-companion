<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;
use Tests\TestCase;

class PetActivityLoggingTest extends TestCase
{
    public function test_creating_activity_logs_system_event(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($user)->create();

        // Associate user as owner via pivot for policy authorization
        \App\Models\PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user->getKey(),
            'role' => 'owner',
        ]);

        $payload = [
            'type' => 'feeding',
            'description' => 'Breakfast meal',
            'media_url' => null,
        ];

        $response = $this->actingAs($user)->postJson('/api/pets/' . $pet->getKey() . '/activities', $payload);
        $response->assertStatus(201);

        $activity = PetActivity::first();
        $this->assertNotNull($activity);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_activity_created',
            'subject_type' => PetActivity::class,
            'subject_id' => $activity->getKey(),
        ]);
    }

    public function test_deleting_activity_logs_system_event(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($user)->create();

        // Associate user as owner via pivot for policy (delete uses owner check)
        \App\Models\PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user->getKey(),
            'role' => 'owner',
        ]);

        $activity = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'walk',
            'description' => 'Afternoon walk',
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/activities/' . $activity->getKey());
        $response->assertStatus(200);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_activity_deleted',
            'subject_type' => PetActivity::class,
            'subject_id' => $activity->getKey(),
        ]);
    }
}
