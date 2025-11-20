<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetRoutine;
use App\Models\PetUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Pet Routine CRUD and management.
 */
class PetRoutineManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_routine_for_their_pet(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        $payload = [
            'name' => 'Morning Walk',
            'description' => 'Daily morning walk around the neighborhood',
            'time_of_day' => '08:00',
            'days_of_week' => [1, 2, 3, 4, 5], // Mon-Fri
        ];

        $response = $this->actingAs($owner)->postJson("/api/pets/{$pet->getKey()}/routines", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'pet_id', 'name', 'time_of_day', 'days_of_week']]);

        $this->assertDatabaseHas('pet_routines', [
            'pet_id' => $pet->getKey(),
            'name' => 'Morning Walk',
            'time_of_day' => '08:00',
        ]);

        // Verify activity log
        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_routine_created',
            'subject_type' => PetRoutine::class,
        ]);
    }

    public function test_caregiver_cannot_create_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $caregiver */
        $caregiver = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'role' => 'caregiver',
        ]);

        $payload = [
            'name' => 'Unauthorized Routine',
            'time_of_day' => '10:00',
            'days_of_week' => [1, 2],
        ];

        $response = $this->actingAs($caregiver)->postJson("/api/pets/{$pet->getKey()}/routines", $payload);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('pet_routines', [
            'pet_id' => $pet->getKey(),
            'name' => 'Unauthorized Routine',
        ]);
    }

    public function test_owner_can_update_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        $routine = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Original Name',
            'time_of_day' => '08:00',
            'days_of_week' => [1, 2, 3],
        ]);

        $payload = [
            'name' => 'Updated Name',
            'time_of_day' => '09:00',
            'days_of_week' => [1, 2, 3, 4, 5],
        ];

        $response = $this->actingAs($owner)->patchJson("/api/routines/{$routine->getKey()}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('pet_routines', [
            'id' => $routine->getKey(),
            'name' => 'Updated Name',
        ]);

        $routine->refresh();
        $this->assertEquals('09:00:00', $routine->time_of_day);

        // Verify activity log
        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_routine_updated',
            'subject_type' => PetRoutine::class,
            'subject_id' => $routine->getKey(),
        ]);
    }

    public function test_caregiver_cannot_update_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $caregiver */
        $caregiver = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'role' => 'caregiver',
        ]);

        $routine = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Original Name',
            'time_of_day' => '08:00',
            'days_of_week' => [1, 2],
        ]);

        $payload = [
            'name' => 'Unauthorized Update',
        ];

        $response = $this->actingAs($caregiver)->patchJson("/api/routines/{$routine->getKey()}", $payload);

        $response->assertStatus(403);

        $this->assertDatabaseHas('pet_routines', [
            'id' => $routine->getKey(),
            'name' => 'Original Name',
        ]);
    }

    public function test_owner_can_delete_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        $routine = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'To Be Deleted',
            'time_of_day' => '08:00',
            'days_of_week' => [1],
        ]);

        $routineId = $routine->getKey();

        $response = $this->actingAs($owner)->deleteJson("/api/routines/{$routineId}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('pet_routines', [
            'id' => $routineId,
        ]);

        // Verify activity log
        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_routine_deleted',
        ]);
    }

    public function test_caregiver_cannot_delete_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $caregiver */
        $caregiver = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'role' => 'caregiver',
        ]);

        $routine = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Should Not Delete',
            'time_of_day' => '08:00',
            'days_of_week' => [1],
        ]);

        $response = $this->actingAs($caregiver)->deleteJson("/api/routines/{$routine->getKey()}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('pet_routines', [
            'id' => $routine->getKey(),
            'name' => 'Should Not Delete',
        ]);
    }

    public function test_owner_can_list_routines_for_their_pet(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Morning Walk',
            'time_of_day' => '08:00',
            'days_of_week' => [1, 2, 3],
        ]);

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Evening Feed',
            'time_of_day' => '18:00',
            'days_of_week' => [1, 2, 3, 4, 5, 6, 0],
        ]);

        $response = $this->actingAs($owner)->getJson("/api/pets/{$pet->getKey()}/routines");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Morning Walk')
            ->assertJsonPath('data.1.name', 'Evening Feed');
    }

    public function test_caregiver_can_list_routines_for_assigned_pet(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $caregiver */
        $caregiver = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $caregiver->getKey(),
            'role' => 'caregiver',
        ]);

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Morning Walk',
            'time_of_day' => '08:00',
            'days_of_week' => [1, 2],
        ]);

        $response = $this->actingAs($caregiver)->getJson("/api/pets/{$pet->getKey()}/routines");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Morning Walk');
    }

    public function test_unauthorized_user_cannot_list_routines(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $stranger */
        $stranger = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Private Routine',
            'time_of_day' => '08:00',
            'days_of_week' => [1],
        ]);

        $response = $this->actingAs($stranger)->getJson("/api/pets/{$pet->getKey()}/routines");

        $response->assertStatus(403);
    }

    public function test_routine_creation_generates_upcoming_occurrences(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        PetUser::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $owner->getKey(),
            'role' => 'owner',
        ]);

        $today = Carbon::today();
        $todayIndex = (int) $today->dayOfWeek;

        $payload = [
            'name' => 'Daily Routine',
            'time_of_day' => '08:00',
            'days_of_week' => [$todayIndex], // Include today
        ];

        $response = $this->actingAs($owner)->postJson("/api/pets/{$pet->getKey()}/routines", $payload);

        $response->assertStatus(201);

        $routine = PetRoutine::where('pet_id', $pet->getKey())->first();

        // Verify at least one occurrence was generated
        $this->assertGreaterThan(0, $routine->occurrences()->count());

        // Verify activity log for occurrence generation
        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_routine_occurrence_generated',
            'subject_type' => PetRoutine::class,
            'subject_id' => $routine->getKey(),
        ]);
    }
}
