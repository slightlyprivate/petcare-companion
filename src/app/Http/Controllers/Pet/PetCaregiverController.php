<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\Pet\PetCaregiverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing pet caregivers.
 *
 * @authenticated
 *
 * @group Pets
 */
class PetCaregiverController extends Controller
{
    public function __construct(private PetCaregiverService $caregiverService) {}

    /**
     * List all caregivers for a specific pet.
     *
     * Returns the list of users (owners and caregivers) associated with the pet.
     *
     * @urlParam pet string required The UUID of the pet. Example: 9d4e8c5a-1234-5678-9abc-def012345678
     */
    public function index(Request $request, Pet $pet): JsonResponse
    {
        $this->authorize('view', $pet);

        $caregivers = $this->caregiverService->listCaregivers($pet);

        return response()->json(['data' => $caregivers], 200);
    }

    /**
     * Remove a caregiver from a pet.
     *
     * Only the pet owner can remove caregivers.
     *
     * @urlParam pet string required The UUID of the pet. Example: 9d4e8c5a-1234-5678-9abc-def012345678
     * @urlParam user string required The UUID of the user to remove. Example: 9d4e8c5a-1234-5678-9abc-def012345679
     */
    public function destroy(Request $request, Pet $pet, string $userId): JsonResponse
    {
        $this->authorize('update', $pet);

        $result = $this->caregiverService->removeCaregiver($pet, $userId, $request->user());

        return response()->json($result['body'], $result['status']);
    }
}
