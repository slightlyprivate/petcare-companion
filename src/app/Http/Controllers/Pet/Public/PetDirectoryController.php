<?php

namespace App\Http\Controllers\Pet\Public;

use App\Helpers\PetPaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetDirectoryListRequest;
use App\Http\Resources\Pet\Directory\DirectoryPetResource;
use App\Services\Pet\PetService;

/**
 * Controller for pet directory.
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
     * Get directory listing of all public pets with donation metadata.
     *
     * Returns public pets sorted by popularity (donation count) by default.
     * Includes total donations and donation count for discovery.
     */
    public function index(PetDirectoryListRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $helper = new PetPaginationHelper($request);

        $pets = $this->petService->directoryList($helper);

        return DirectoryPetResource::collection($pets);
    }
}
