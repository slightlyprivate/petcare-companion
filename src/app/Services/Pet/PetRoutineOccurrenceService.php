<?php

namespace App\Services\Pet;

use App\Models\Pet;
use App\Models\PetRoutine;
use App\Models\PetRoutineOccurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service handling retrieval and completion of pet routine occurrences.
 */
class PetRoutineOccurrenceService
{
    /**
     * Get (and lazily create) today's occurrences for a pet.
     *
     * @return Collection<int,PetRoutineOccurrence>
     */
    public function todayForPet(Pet $pet): Collection
    {
        $today = Carbon::today();
        $dayIndex = (int) $today->dayOfWeek; // 0 (Sun) .. 6 (Sat)

        // Load routines scheduled for today.
        $routines = PetRoutine::query()
            ->where('pet_id', $pet->getKey())
            ->whereJsonContains('days_of_week', $dayIndex)
            ->orderBy('time_of_day')
            ->get();

        $occurrences = collect();

        DB::transaction(function () use ($routines, $today, $occurrences) {
            foreach ($routines as $routine) {
                /** @var PetRoutine $routine */
                $occurrence = $routine->occurrences()
                    ->whereDate('date', $today->toDateString())
                    ->first();
                if (! $occurrence) {
                    $occurrence = $routine->occurrences()->create([
                        'date' => $today->toDateString(),
                    ]);
                    activity()
                        ->performedOn($routine)
                        ->withProperties([
                            'pet_id' => $routine->pet_id,
                            'routine_id' => $routine->getKey(),
                            'occurrence_id' => $occurrence->getKey(),
                            'date' => $today->toDateString(),
                        ])
                        ->log('pet_routine_occurrence_generated');
                }
                $occurrences->push($occurrence->load('routine'));
            }
        });

        return $occurrences;
    }

    /**
     * Generate upcoming occurrences for a routine for the next N days.
     * Skips existing occurrences.
     *
     * @return Collection<int,PetRoutineOccurrence>
     */
    public function generateUpcoming(PetRoutine $routine, int $days = 7): Collection
    {
        $created = collect();
        $today = Carbon::today();
        $scheduleDays = collect($routine->days_of_week)->map(fn ($d) => (int) $d)->all();

        DB::transaction(function () use ($routine, $days, $today, $scheduleDays, $created) {
            for ($offset = 0; $offset < $days; $offset++) {
                $date = $today->copy()->addDays($offset);
                $dayIndex = (int) $date->dayOfWeek;
                if (! in_array($dayIndex, $scheduleDays, true)) {
                    continue; // Not scheduled this day
                }
                $existing = $routine->occurrences()->whereDate('date', $date->toDateString())->first();
                if ($existing) {
                    continue; // Already exists
                }
                $occurrence = $routine->occurrences()->create([
                    'date' => $date->toDateString(),
                ]);
                $created->push($occurrence);
                activity()
                    ->performedOn($routine)
                    ->withProperties([
                        'pet_id' => $routine->pet_id,
                        'routine_id' => $routine->getKey(),
                        'occurrence_id' => $occurrence->getKey(),
                        'date' => $date->toDateString(),
                    ])
                    ->log('pet_routine_occurrence_generated');
            }
        });

        return $created;
    }

    /**
     * Mark an occurrence complete.
     */
    public function complete(PetRoutineOccurrence $occurrence, User $user): PetRoutineOccurrence
    {
        if ($occurrence->completed_at) {
            return $occurrence; // Already completed
        }

        $occurrence->forceFill([
            'completed_at' => Carbon::now(),
            'completed_by' => $user->getKey(),
        ])->save();

        activity()
            ->performedOn($occurrence->routine)
            ->withProperties([
                'pet_id' => $occurrence->routine->pet_id,
                'routine_id' => $occurrence->routine->getKey(),
                'occurrence_id' => $occurrence->getKey(),
                'completed_by' => $user->getKey(),
            ])
            ->log('pet_routine_occurrence_completed');

        return $occurrence->load('routine');
    }
}
