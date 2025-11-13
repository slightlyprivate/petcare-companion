<?php

namespace App\Services\Pet;

use App\Helpers\NotificationHelper;
use App\Helpers\PetPaginationHelper;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\PetUpdatedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Service for managing pets.
 *
 * @group Pets
 */
class PetService
{
    /**
     * Create a new pet.
     */
    public function create(array $data): Pet
    {
        if (! isset($data['user_id'])) {
            $data['user_id'] = Auth::user()->id;
        }

        return Pet::create($data);
    }

    /**
     * Update an existing pet.
     */
    public function update(Pet $pet, array $data): Pet
    {
        // Track changes for notification
        $changes = array_diff_assoc($data, $pet->getAttributes());

        $pet->update($data);

        // Send pet updated notification to owner if there are changes and preference is enabled
        if (! empty($changes) && $pet->user && NotificationHelper::isNotificationEnabled($pet->user, 'pet_update')) {
            Notification::send($pet->user, new PetUpdatedNotification($pet, $changes));
        }

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
        /** @var User $user */
        $user = Auth::user();

        $query = Pet::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

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

    /**
     * Get a paginated list of public pets with filtering and sorting, including donation totals.
     */
    public function directoryList(PetPaginationHelper $helper): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Pet::where('is_public', true);

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

        // Apply sorting (with popularity option)
        $allowedSortFields = ['name', 'species', 'breed', 'owner_name', 'birth_date', 'created_at', 'popularity'];
        $sortBy = $helper->getSortBy();
        $sortDirection = $helper->getSortDirection();

        if ($sortBy === 'popularity') {
            // Sort by total donations (descending by default)
            $query->withCount(['donations' => function ($q) {
                $q->where('status', 'paid');
            }])
                ->orderBy('donations_count', $sortDirection === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy && in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        // Load donation totals for all results
        $query->withSum(['donations' => function ($q) {
            $q->where('status', 'paid');
        }], 'amount_cents');

        // Apply pagination
        $perPage = $helper->getPerPage();

        return $query->paginate($perPage);
    }
}
