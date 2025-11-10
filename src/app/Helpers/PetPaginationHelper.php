<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class PetPaginationHelper extends PaginationHelper
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  /**
   * Get the filters specific to pets.
   */
  public function getFilters(): array
  {
    return $this->request->only(['species', 'owner_name', 'name']);
  }
}
