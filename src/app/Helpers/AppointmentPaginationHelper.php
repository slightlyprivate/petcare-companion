<?php

namespace App\Helpers;

/**
 * Helper class for paginating appointment listings.
 */
class AppointmentPaginationHelper extends PaginationHelper
{
    /**
     * Create a new AppointmentPaginationHelper instance.
     */
    public function __construct(\Illuminate\Http\Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Get the filters specific to appointments.
     */
    public function getFilters(): array
    {
        return $this->request->only(['status', 'from_date', 'to_date', 'search']);
    }
}
