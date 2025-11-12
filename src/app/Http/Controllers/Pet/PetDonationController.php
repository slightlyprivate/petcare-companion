<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationStoreRequest;
use App\Models\Pet;
use App\Services\Pet\PetDonationService;

/**
 * Controller for handling pet donation operations.
 *
 * @group Donations
 */
class PetDonationController extends Controller
{
    /** @var PetDonationService */
    protected $petDonationService;

    /**
     * Create a new controller instance.
     */
    public function __construct(PetDonationService $petDonationService)
    {
        $this->petDonationService = $petDonationService;
    }

    /**
     * Create a donation for a specific pet.
     */
    public function store(DonationStoreRequest $request, Pet $pet): \Illuminate\Http\JsonResponse
    {
        $requestData = $request->validated();
        $requestUser = $request->user();

        try {

            $donation = $this->petDonationService->createDonation($requestData, $requestUser, $pet);

            return response()->json($donation, 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to create payment session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
