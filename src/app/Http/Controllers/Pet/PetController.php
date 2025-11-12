<?php

namespace App\Http\Controllers\Pet;

use App\Helpers\PetPaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PetListRequest;
use App\Http\Requests\PetShowRequest;
use App\Http\Requests\PetStoreRequest;
use App\Http\Resources\PetResource;
use App\Models\Pet;
use App\Services\Pet\PetService;

/**
 * Controller for managing pets.
 *
 * @group Pets
 */
class PetController extends Controller
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
     * Store a newly created pet in storage.
     */
    public function store(PetStoreRequest $request): PetResource
    {
        $pet = $this->petService->create($request->validated());

        return new PetResource($pet);
    }

    /**
     * Update the specified pet in storage.
     */
    public function update(PetStoreRequest $request, Pet $pet): PetResource
    {
        $this->authorize('update', $pet);

        $pet = $this->petService->update($pet, $request->validated());

        return new PetResource($pet);
    }

    /**
     * Remove the specified pet from storage.
     */
    public function destroy(Pet $pet): \Illuminate\Http\Response
    {
        $this->authorize('delete', $pet);

        $this->petService->delete($pet);

        return response()->noContent();
    }

    /**
     * Get a listing of all pets.
     */
    public function index(PetListRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $helper = new PetPaginationHelper($request);

        $pets = $this->petService->list($helper);

        return PetResource::collection($pets);
    }

    /**
     * Get the specified pet.
     */
    public function show(PetShowRequest $request, Pet $pet): PetResource
    {
        $this->authorize('view', $pet);

        // Load appointments if requested
        if ($request->query('include') === 'appointments') {
            $pet->load('appointments');
        }

        return new PetResource($pet);
    }
}
