<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetRoutineOccurrence;
use App\Policies\PetRoutinePolicy;
use App\Services\Pet\PetRoutineOccurrenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for viewing and completing pet routine occurrences.
 *
 * @group Pets
 *
 * @authenticated
 */
class PetRoutineOccurrenceController extends Controller
{
    public function __construct(private PetRoutineOccurrenceService $service) {}

    /**
     * Get today's routine occurrences for a pet.
     */
    public function today(Request $request, Pet $pet): JsonResponse
    {
        if (! app(PetRoutinePolicy::class)->viewAny($request->user(), $pet)) {
            abort(403, 'Not authorized to view routine tasks for this pet.');
        }

        $occurrences = $this->service->todayForPet($pet);

        return response()->json([
            'data' => $occurrences->map(fn(PetRoutineOccurrence $o) => $this->transform($o)),
        ]);
    }

    /**
     * Complete a routine occurrence.
     */
    public function complete(Request $request, PetRoutineOccurrence $occurrence): JsonResponse
    {
        $this->authorize('complete', $occurrence->routine);
        $completed = $this->service->complete($occurrence, $request->user());

        return response()->json(['data' => $this->transform($completed)]);
    }

    /**
     * Transform occurrence into API-friendly array.
     *
     * @return array<string,mixed>
     */
    protected function transform(PetRoutineOccurrence $occurrence): array
    {
        $routine = $occurrence->routine;

        return [
            'id' => $occurrence->id,
            'routine_id' => $occurrence->pet_routine_id,
            'date' => $occurrence->date?->toDateString(),
            'completed_at' => $occurrence->completed_at?->toIso8601String(),
            'completed_by' => $occurrence->completed_by,
            'routine' => [
                'id' => $routine->id,
                'pet_id' => $routine->pet_id,
                'name' => $routine->name,
                'description' => $routine->description,
                'time_of_day' => $routine->time_of_day,
                'days_of_week' => $routine->days_of_week,
            ],
        ];
    }
}
