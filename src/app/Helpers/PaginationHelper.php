<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class PaginationHelper
{
  protected Request $request;

  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  /**
   * Get the current page number.
   */
  public function getPage(): int
  {
    return (int) $this->request->get('page', 1);
  }

  /**
   * Get the number of items per page.
   */
  public function getPerPage(): int
  {
    return min((int) $this->request->get('per_page', 15), 50);
  }

  /**
   * Get the sort by field.
   */
  public function getSortBy(): ?string
  {
    return $this->request->get('sort_by');
  }

  /**
   * Get the sort direction.
   */
  public function getSortDirection(): string
  {
    $direction = $this->request->get('sort_direction', 'asc');
    return in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
  }

  /**
   * Get the filters from the request.
   */
  public function getFilters(): array
  {
    // This can be overridden in subclasses or extended for specific filters
    return [];
  }

  /**
   * Calculate the offset for pagination.
   */
  public function calculateOffset(): int
  {
    return ($this->getPage() - 1) * $this->getPerPage();
  }

  /**
   * Get the sort options as an array.
   */
  public function getSortOptions(): array
  {
    return [
      'sort_by' => $this->getSortBy(),
      'sort_direction' => $this->getSortDirection(),
    ];
  }
}
