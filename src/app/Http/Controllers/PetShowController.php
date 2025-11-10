<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Http\Resources\PetResource;
use Illuminate\Http\Request;

class PetShowController extends Controller
{
  /**
   * Display the specified pet.
   */
  public function __invoke(Pet $pet, Request $request): PetResource
  {
    // Load appointments if requested
    if ($request->query('include') === 'appointments') {
      $pet->load('appointments');
    }

    return new PetResource($pet);
  }
}
