<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\PetActivity\CreatePetActivityRequest;
use App\Http\Requests\PetActivity\ListPetActivitiesRequest;
use App\Models\Pet;
use App\Models\PetActivity;
use App\Services\Pet\PetActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing pet activities.
 *
 * @authenticated
 *
 * @group Pets
 */
class PetActivityController extends Controller
{
    public function __construct(private PetActivityService $activityService) {}

    /**
     * List activities for a pet.
     *
     * @urlParam pet string required The UUID of the pet.
     *
     * @queryParam per_page int Number of activities per page (1-100). Example: 15
     * @queryParam type string Filter by activity type. Example: feeding
     * @queryParam date_from date Filter activities created on or after this date (YYYY-MM-DD). Example: 2025-11-01
     * @queryParam date_to date Filter activities created on or before this date (YYYY-MM-DD). Example: 2025-11-19
     */
    public function index(ListPetActivitiesRequest $request, Pet $pet): JsonResponse
    {
        $validated = $request->validated();
        $activities = $this->activityService->listForPet($pet, $validated);

        return response()->json([
            'data' => $activities->items(),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
        ], 200);
    }

    /**
     * Store a new activity for a pet.
     *
     * @urlParam pet string required The UUID of the pet.
     *
     * @bodyParam type string required The activity type. Example: feeding
     * @bodyParam description string required A short description of the activity. Example: Morning feeding with new kibble
     * @bodyParam media_url string nullable Optional media URL associated with the activity. Example: https://example.com/image.jpg
     */
    public function store(CreatePetActivityRequest $request, Pet $pet): JsonResponse
    {
        $this->authorize('create', [\App\Models\PetActivity::class, $pet]);
        $data = $request->validated();

        $activity = $this->activityService->create($pet, $request->user(), $data);

        return response()->json([
            'message' => __('activity.created.success'),
            'data' => $activity,
        ], 201);
    }

    /**
     * Remove an activity.
     *
     * @urlParam activity integer required The ID of the activity to delete.
     */
    public function destroy(Request $request, PetActivity $activity): JsonResponse
    {
        $this->authorize('delete', $activity);
        $this->activityService->delete($activity, $request->user());

        return response()->json([
            'message' => __('activity.deleted.success'),
        ], 200);
    }
}
