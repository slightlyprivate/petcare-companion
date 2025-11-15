<?php

namespace App\Http\Controllers\GiftType;

use App\Http\Controllers\Controller;
use App\Http\Requests\GiftType\StoreGiftTypeRequest;
use App\Http\Requests\GiftType\UpdateGiftTypeRequest;
use App\Http\Resources\GiftTypeResource;
use App\Models\GiftType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller for managing gift types.
 *
 * Provides both admin CRUD operations and public catalog access.
 *
 * @group Gift Types
 */
class GiftTypeController extends Controller
{
    /**
     * Get all active gift types (public catalog).
     *
     * Returns a sorted list of active gift types for client applications.
     * No authentication required.
     */
    public function index(): AnonymousResourceCollection
    {
        $giftTypes = GiftType::visible()->get();

        return GiftTypeResource::collection($giftTypes);
    }

    /**
     * Get a specific gift type details (public).
     *
     * Returns full details of a gift type if it's active.
     * No authentication required.
     */
    public function show(GiftType $giftType): GiftTypeResource
    {
        return new GiftTypeResource($giftType);
    }

    /**
     * Create a new gift type (admin only).
     */
    public function store(StoreGiftTypeRequest $request): JsonResponse
    {
        $giftType = GiftType::create($request->validated());

        return response()->json([
            'data' => new GiftTypeResource($giftType),
        ], 201);
    }

    /**
     * Update a gift type (admin only).
     */
    public function update(UpdateGiftTypeRequest $request, GiftType $giftType): GiftTypeResource
    {
        $giftType->update($request->validated());

        return new GiftTypeResource($giftType);
    }

    /**
     * Delete a gift type (admin only).
     */
    public function destroy(GiftType $giftType): JsonResponse
    {
        $this->authorize('delete', $giftType);

        $giftType->delete();

        return response()->json(null, 204);
    }
}
