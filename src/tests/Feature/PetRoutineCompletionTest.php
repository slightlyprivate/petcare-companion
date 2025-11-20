<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetRoutine;
use App\Models\PetRoutineOccurrence;
use App\Models\PetUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Pet Routine Occurrence completion workflow.
 */
class PetRoutineCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_complete_routine_occurrence(): void
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
            'name' => 'Morning Feed',
            'time_of_day' => '08:00',
            'days_of_week' => [1, 2, 3, 4, 5],
        ]);

        $occurrence = PetRoutineOccurrence::create([
            'pet_routine_id' => $routine->getKey(),
            'date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($owner)->postJson("/api/routine-occurrences/{$occurrence->getKey()}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('data.completed_at', fn ($value) => $value !== null)
            ->assertJsonPath('data.completed_by', $owner->getKey());

        $this->assertDatabaseHas('pet_routine_occurrences', [
            'id' => $occurrence->getKey(),
            'completed_by' => $owner->getKey(),
        ]);

        $occurrence->refresh();
        $this->assertNotNull($occurrence->completed_at);

        // Verify activity log
        $this->assertDatabaseHas('activity_log', [
            'description' => 'pet_routine_occurrence_completed',
            'subject_type' => PetRoutine::class,
            'subject_id' => $routine->getKey(),
        ]);
    }

    public function test_caregiver_can_complete_routine_occurrence(): void
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
            'name' => 'Evening Walk',
            'time_of_day' => '18:00',
            'days_of_week' => [1, 2, 3],
        ]);

        $occurrence = PetRoutineOccurrence::create([
            'pet_routine_id' => $routine->getKey(),
            'date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($caregiver)->postJson("/api/routine-occurrences/{$occurrence->getKey()}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('data.completed_by', $caregiver->getKey());

        $this->assertDatabaseHas('pet_routine_occurrences', [
            'id' => $occurrence->getKey(),
            'completed_by' => $caregiver->getKey(),
        ]);
    }

    public function test_unauthorized_user_cannot_complete_routine_occurrence(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $stranger */
        $stranger = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        $routine = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Private Routine',
            'time_of_day' => '10:00',
            'days_of_week' => [1],
        ]);

        $occurrence = PetRoutineOccurrence::create([
            'pet_routine_id' => $routine->getKey(),
            'date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($stranger)->postJson("/api/routine-occurrences/{$occurrence->getKey()}/complete");

        $response->assertStatus(403);

        $this->assertDatabaseHas('pet_routine_occurrences', [
            'id' => $occurrence->getKey(),
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }

    public function test_completing_already_completed_occurrence_is_idempotent(): void
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
            'name' => 'Morning Routine',
            'time_of_day' => '08:00',
            'days_of_week' => [1],
        ]);

        $firstCompletedAt = Carbon::now()->subMinutes(10);

        $occurrence = PetRoutineOccurrence::create([
            'pet_routine_id' => $routine->getKey(),
            'date' => Carbon::today()->toDateString(),
            'completed_at' => $firstCompletedAt,
            'completed_by' => $owner->getKey(),
        ]);

        $response = $this->actingAs($owner)->postJson("/api/routine-occurrences/{$occurrence->getKey()}/complete");

        $response->assertStatus(200);

        $occurrence->refresh();
        $this->assertEquals($firstCompletedAt->timestamp, $occurrence->completed_at->timestamp);
        $this->assertEquals($owner->getKey(), $occurrence->completed_by);
    }

    public function test_today_tasks_endpoint_returns_todays_occurrences(): void
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

        $routine1 = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Morning Routine',
            'time_of_day' => '08:00',
            'days_of_week' => [$todayIndex],
        ]);

        $routine2 = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Evening Routine',
            'time_of_day' => '18:00',
            'days_of_week' => [$todayIndex],
        ]);

        // Routine not scheduled today
        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Weekend Only',
            'time_of_day' => '10:00',
            'days_of_week' => [0, 6], // Sunday & Saturday
        ]);

        $response = $this->actingAs($owner)->getJson("/api/pets/{$pet->getKey()}/routines/today");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Verify occurrences were created lazily
        $this->assertEquals(1, $routine1->occurrences()->whereDate('date', $today->toDateString())->count());
        $this->assertEquals(1, $routine2->occurrences()->whereDate('date', $today->toDateString())->count());
    }

    public function test_caregiver_can_view_today_tasks(): void
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

        $today = Carbon::today();
        $todayIndex = (int) $today->dayOfWeek;

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Daily Task',
            'time_of_day' => '10:00',
            'days_of_week' => [$todayIndex],
        ]);

        $response = $this->actingAs($caregiver)->getJson("/api/pets/{$pet->getKey()}/routines/today");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.routine.name', 'Daily Task');
    }

    public function test_unauthorized_user_cannot_view_today_tasks(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $stranger */
        $stranger = User::factory()->create();
        /** @var Pet $pet */
        $pet = Pet::factory()->for($owner)->create();

        $today = Carbon::today();
        $todayIndex = (int) $today->dayOfWeek;

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Private Routine',
            'time_of_day' => '10:00',
            'days_of_week' => [$todayIndex],
        ]);

        $response = $this->actingAs($stranger)->getJson("/api/pets/{$pet->getKey()}/routines/today");

        $response->assertStatus(403);
    }

    public function test_today_tasks_ordered_by_time_of_day(): void
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

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Evening Task',
            'time_of_day' => '18:00',
            'days_of_week' => [$todayIndex],
        ]);

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Morning Task',
            'time_of_day' => '08:00',
            'days_of_week' => [$todayIndex],
        ]);

        PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => 'Afternoon Task',
            'time_of_day' => '14:00',
            'days_of_week' => [$todayIndex],
        ]);

        $response = $this->actingAs($owner)->getJson("/api/pets/{$pet->getKey()}/routines/today");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.routine.name', 'Morning Task')
            ->assertJsonPath('data.1.routine.name', 'Afternoon Task')
            ->assertJsonPath('data.2.routine.name', 'Evening Task');
    }
}
