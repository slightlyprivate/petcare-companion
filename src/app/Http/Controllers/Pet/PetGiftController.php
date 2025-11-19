<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\StoreGiftRequest;
use App\Models\Pet;
use App\Services\Pet\PetGiftService;

/**
 * Controller for managing pet gifts.
 *
 * @authenticated
 *
 * @group Gifts
 */
class PetGiftController extends Controller
{
    /** @var PetGiftService */
    protected $petGiftService;

    /**
     * Create a new controller instance.
     */
    public function __construct(PetGiftService $petGiftService)
    {
        $this->petGiftService = $petGiftService;
    }

    /**
     * Send a gift to a specific pet.
     */
    public function store(StoreGiftRequest $request, Pet $pet): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $pet);

        $requestData = $request->validated();
        $requestUser = $request->user();

        $gift = $this->petGiftService->createGift($requestData, $requestUser, $pet);

        return response()->json($gift, 201);
    }
}
