<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Response;

class PetDeleteController extends Controller
{
  /**
   * Remove the specified pet from storage.
   */
  public function __invoke(Pet $pet): Response
  {
    $pet->delete();

    return response()->noContent();
  }
}
