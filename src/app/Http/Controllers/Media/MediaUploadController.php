<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Services\Media\MediaStorageService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for handling media uploads.
 *
 * @authenticated
 *
 * @group Media
 */
class MediaUploadController extends Controller
{
    public function __construct(private MediaStorageService $mediaStorage) {}

    /**
     * Handle authenticated media uploads and return the stored path + public URL.
     *
     * @authenticated
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->mediaStorage->store(
            $request->file('file'),
            $validated['context'] ?? 'activities'
        );

        return response()->json([
            'message' => __('media.upload.success'),
            'data' => $result,
        ], 201);
    }
}
