<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\RestorePetRequest;
use App\Models\Pet;
use App\Services\Pet\PetService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * Controller for restoring soft-deleted pets.
 *
 * @group Pets
 */
class PetRestoreController extends Controller
{
    /** @var PetService */
    protected $petService;

    /**
     * Create a new controller instance.
     */
    public function __construct(PetService $petService)
    {
        $this->petService = $petService;
    }

    /**
     * Restore a soft-deleted pet.
     *
     * @throws AuthorizationException
     */
    public function restore(RestorePetRequest $request, string $pet): JsonResponse
    {
        // Retrieve pet including soft-deleted ones
        $petModel = Pet::withTrashed()->findOrFail($pet);

        // Check if user owns this pet
        if ($petModel->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized to restore this pet');
        }

        // Check if pet is already not deleted
        if (! $petModel->trashed()) {
            return response()->json([
                'message' => 'Pet is not deleted',
                'pet' => $petModel,
            ], 200);
        }

        try {
            $this->petService->restore($petModel);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('pet.restore.failure'),
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => __('pet.restore.success'),
            'pet' => $petModel,
        ], 200);
    }
}
