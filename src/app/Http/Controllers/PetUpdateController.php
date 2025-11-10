<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Http\Requests\StorePetRequest;
use App\Http\Resources\PetResource;

class PetUpdateController extends Controller
{
  /**
   * Update the specified pet in storage.
   */
  public function __invoke(StorePetRequest $request, Pet $pet): PetResource
  {
    $pet->update($request->validated());

    return new PetResource($pet->fresh());
  }
}
