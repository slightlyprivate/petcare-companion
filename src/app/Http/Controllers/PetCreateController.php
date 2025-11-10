<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Http\Requests\StorePetRequest;
use App\Http\Resources\PetResource;
use Illuminate\Http\JsonResponse;

class PetCreateController extends Controller
{
    /**
     * Store a newly created pet in storage.
     */
    public function __invoke(StorePetRequest $request): JsonResponse
    {
        $pet = Pet::create($request->validated());

        return (new PetResource($pet))
            ->response()
            ->setStatusCode(201);
    }
}
