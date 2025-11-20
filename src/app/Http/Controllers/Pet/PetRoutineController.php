<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\PetRoutine\CreatePetRoutineRequest;
use App\Http\Requests\PetRoutine\DeletePetRoutineRequest;
use App\Http\Requests\PetRoutine\ListPetRoutinesRequest;
use App\Http\Requests\PetRoutine\UpdatePetRoutineRequest;
use App\Models\Pet;
use App\Models\PetRoutine;
use App\Services\Pet\PetRoutineService;
use Illuminate\Http\JsonResponse;

/**
 * Controller providing CRUD operations for pet routines.
 *
 * @group Pets
 *
 * @authenticated
 */
class PetRoutineController extends Controller
{
    public function __construct(private PetRoutineService $service) {}

    /**
     * List routines for a given pet.
     */
    public function index(ListPetRoutinesRequest $request, Pet $pet): JsonResponse
    {
        // Authorization: owners & caregivers can view routines
        if (! app(\App\Policies\PetRoutinePolicy::class)->viewAny($request->user(), $pet)) {
            abort(403, 'Not authorized to view routines for this pet.');
        }
        $routines = $this->service->listForPet($pet, $request->validated());

        return response()->json([
            'data' => $routines->map(fn (PetRoutine $routine) => $this->transform($routine)),
        ]);
    }

    /**
     * Create a new routine for the pet.
     */
    public function store(CreatePetRoutineRequest $request, Pet $pet): JsonResponse
    {
        $this->authorize('create', [\App\Models\PetRoutine::class, $pet]);
        $routine = $this->service->create($pet, $request->validated());

        return response()->json(['data' => $this->transform($routine)], 201);
    }

    /**
     * Update an existing routine.
     */
    public function update(UpdatePetRoutineRequest $request, PetRoutine $routine): JsonResponse
    {
        $this->authorize('update', $routine);
        $updated = $this->service->update($routine, $request->validated());

        return response()->json(['data' => $this->transform($updated)]);
    }

    /**
     * Delete a routine.
     */
    public function destroy(DeletePetRoutineRequest $request, PetRoutine $routine): JsonResponse
    {
        $this->authorize('delete', $routine);
        $this->service->delete($routine);

        return response()->json([], 204);
    }

    /**
     * Transform a routine model into API-friendly array.
     *
     * @return array<string, mixed>
     */
    protected function transform(PetRoutine $routine): array
    {
        return [
            'id' => $routine->id,
            'pet_id' => $routine->pet_id,
            'name' => $routine->name,
            'description' => $routine->description,
            'time_of_day' => $routine->time_of_day,
            'days_of_week' => $routine->days_of_week,
            'created_at' => $routine->created_at?->toIso8601String(),
            'updated_at' => $routine->updated_at?->toIso8601String(),
        ];
    }
}
