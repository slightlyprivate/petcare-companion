<?php

namespace App\Services\Pet;

use App\Models\Pet;
use App\Models\PetRoutine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing pet routines.
 *
 * @group Services
 */
class PetRoutineService
{
    /**
     * List routines for a pet applying optional filters.
     *
     * @param  array<string,mixed>  $filters
     * @return Collection<int, PetRoutine>
     */
    public function listForPet(Pet $pet, array $filters): Collection
    {
        $query = PetRoutine::query()->where('pet_id', $pet->getKey())->orderBy('time_of_day');

        if (! empty($filters['day'])) {
            $query->whereJsonContains('days_of_week', (int) $filters['day']);
        }

        return $query->get();
    }

    /**
     * Create a routine for a pet.
     *
     * @param  array<string,mixed>  $data
     */
    public function create(Pet $pet, array $data): PetRoutine
    {
        $routine = PetRoutine::create([
            'pet_id' => $pet->getKey(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'time_of_day' => $data['time_of_day'],
            'days_of_week' => $data['days_of_week'],
        ]);

        // Pre-generate upcoming occurrences (next 7 days)
        app(\App\Services\Pet\PetRoutineOccurrenceService::class)->generateUpcoming($routine, 7);

        activity()
            ->performedOn($routine)
            ->withProperties([
                'pet_id' => $pet->getKey(),
                'name' => $routine->name,
            ])
            ->log('pet_routine_created');

        return $routine;
    }

    /**
     * Update a routine with provided data.
     *
     * @param  array<string,mixed>  $data
     */
    public function update(PetRoutine $routine, array $data): PetRoutine
    {
        $routine->update($data);

        // Regenerate upcoming occurrences (may add newly scheduled days)
        app(\App\Services\Pet\PetRoutineOccurrenceService::class)->generateUpcoming($routine, 7);

        activity()
            ->performedOn($routine)
            ->withProperties([
                'pet_id' => $routine->pet_id,
                'routine_id' => $routine->getKey(),
            ])
            ->log('pet_routine_updated');

        return $routine->refresh();
    }

    /**
     * Delete a routine and its occurrences.
     */
    public function delete(PetRoutine $routine): void
    {
        $routineId = $routine->getKey();
        $petId = $routine->pet_id;

        DB::transaction(function () use ($routine) {
            $routine->occurrences()->delete();
            $routine->delete();
        });

        activity()
            ->performedOn($routine)
            ->withProperties([
                'pet_id' => $petId,
                'routine_id' => $routineId,
            ])
            ->log('pet_routine_deleted');
    }
}
