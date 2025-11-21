<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetAvatarUploadRequest;
use App\Models\Pet;
use App\Services\Media\MediaStorageService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for managing pet avatar uploads.
 *
 * @group Pets
 */
class PetAvatarController extends Controller
{
    public function __construct(private MediaStorageService $storage) {}

    /**
     * Upload a pet avatar image.
     */
    public function store(PetAvatarUploadRequest $request, Pet $pet): JsonResponse
    {
        $file = $request->file('avatar');

        // Delete old avatar if present
        if ($pet->avatar_path) {
            $this->storage->deleteIfLocal($pet->avatar_path);
        }

        // Store new avatar with pet_avatars context
        $result = $this->storage->store($file, 'pet_avatars');

        // Update pet with relative path
        $pet->avatar_path = $result['path'];
        $pet->save();

        activity('pet.avatar_uploaded')
            ->performedOn($pet)
            ->withProperties(['path' => $result['path']])
            ->log('Avatar uploaded');

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => $pet->avatar_url,
        ], 200);
    }
}
