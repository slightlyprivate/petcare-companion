<?php

namespace App\Services\Pet;

use App\Helpers\AppointmentPaginationHelper;
use App\Models\Appointment;
use App\Models\Pet;

/**
 * Service for managing pet appointments.
 *
 * @group Appointments
 */
class PetAppointmentService
{
    /**
     * Create a new appointment for a pet.
     */
    public function create(Pet $pet, array $data): Appointment
    {
        return $pet->appointments()->create($data);
    }

    /**
     * Get a paginated list of pet appointments with filtering and sorting.
     */
    public function list(Pet $pet, AppointmentPaginationHelper $helper): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $pet->appointments();

        $filters = $helper->getFilters();
        $sortBy = $helper->getSortBy();
        $sortDirection = $helper->getSortDirection();
        $perPage = $helper->getPerPage();

        // Apply filters
        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }
        if (! empty($filters['from_date'])) {
            $query->where('scheduled_at', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->where('scheduled_at', '<=', $filters['to_date']);
        }
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply sorting
        $allowedSortFields = ['scheduled_at', 'title', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('scheduled_at', 'asc');
        }

        // Apply pagination
        $perPage = min($perPage, 50); // Max 50 items per page

        return $query->paginate($perPage);
    }
}
