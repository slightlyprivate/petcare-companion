<?php

namespace App\Services\Pet;

use App\Helpers\PetPaginationHelper;
use App\Models\Pet;

/**
 * Service for managing pets.
 */
class PetService
{
  /**
   * Create a new pet.
   */
  public function create(array $data): Pet
  {
    return Pet::create($data);
  }

  /**
   * Update an existing pet.
   */
  public function update(Pet $pet, array $data): Pet
  {
    $pet->update($data);

    return $pet;
  }

  /**
   * Delete a pet.
   */
  public function delete(Pet $pet): void
  {
    $pet->delete();
  }

  /**
   * Get all pets.
   */
  public function getAll(): \Illuminate\Database\Eloquent\Collection
  {
    return Pet::all();
  }

  /**
   * Find a pet by ID.
   */
  public function findById(int $id): ?Pet
  {
    return Pet::find($id);
  }

  /**
   * Get a paginated list of pets with filtering and sorting.
   */
  public function list(PetPaginationHelper $helper): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    $query = Pet::query();

    $filters = $helper->getFilters();

    // Apply filters
    if (! empty($filters['species'])) {
      $query->bySpecies($filters['species']);
    }
    if (! empty($filters['owner_name'])) {
      $query->byOwner($filters['owner_name']);
    }
    if (! empty($filters['name'])) {
      $query->byName($filters['name']);
    }

    // Apply sorting
    $allowedSortFields = ['name', 'species', 'breed', 'owner_name', 'birth_date', 'created_at'];
    $sortBy = $helper->getSortBy();
    $sortDirection = $helper->getSortDirection();

    if ($sortBy && in_array($sortBy, $allowedSortFields)) {
      $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
    } else {
      $query->orderBy('name', 'asc');
    }

    // Apply pagination
    $perPage = $helper->getPerPage();

    return $query->paginate($perPage);
  }
}
