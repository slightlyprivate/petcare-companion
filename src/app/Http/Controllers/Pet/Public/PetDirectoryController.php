<?php

namespace App\Http\Controllers\Pet\Public;

use App\Helpers\PetPaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\Directory\ListPetDirectoryRequest;
use App\Http\Resources\Pet\Directory\DirectoryPetResource;
use App\Services\Pet\PetService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Controller for pet directory.
 *
 * @unauthenticated
 *
 * @group Pets
 */
class PetDirectoryController extends Controller
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
     * Get directory listing of all public pets with gift metadata.
     *
     * Returns public pets sorted by popularity (gift count) by default.
     * Includes total gifts and gift count for discovery.
     */
    public function index(ListPetDirectoryRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $helper = new PetPaginationHelper($request);

        $pets = $this->petService->directoryList($helper);

        return DirectoryPetResource::collection($pets);
    }

    /**
     * Get a single public pet with gift summaries.
     */
    public function show(string $petId): JsonResource
    {
        $pet = $this->petService->directoryShow($petId);

        return new DirectoryPetResource($pet);
    }
}
