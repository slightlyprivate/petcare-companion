<?php

namespace App\Helpers;

/**
 * Helper class for paginating pet listings.
 */
class PetPaginationHelper extends PaginationHelper
{
    /**
     * Create a new PetPaginationHelper instance.
     */
    public function __construct(\Illuminate\Http\Request $request)
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
