<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class AppointmentPaginationHelper extends PaginationHelper
{
  public function __construct(Request $request)
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
