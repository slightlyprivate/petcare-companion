<?php

namespace App\Services\Appointment;

use App\Helpers\AppointmentPaginationHelper;
use App\Models\Appointment;

/**
 * Service for managing appointments.
 *
 * @group Appointments
 */
class AppointmentService
{
    /**
     * Create a new appointment.
     */
    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }

    /**
     * Update an existing appointment.
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);

        return $appointment;
    }

    /**
     * Delete a appointment.
     */
    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }

    /**
     * Get all appointments.
     */
    public function getAll(): array
    {
        return Appointment::all()->toArray();
    }

    /**
     * Find a appointment by ID.
     */
    public function findById(int $id): ?Appointment
    {
        return Appointment::find($id);
    }

    /**
     * Get a paginated list of appointments with filtering and sorting.
     */
    public function list(AppointmentPaginationHelper $helper): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Appointment::query();

        $filters = $helper->getFilters();

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
        $sortBy = $helper->getSortBy();
        $sortDirection = $helper->getSortDirection();

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('scheduled_at', 'asc');
        }

        // Apply pagination
        $perPage = $helper->getPerPage();

        return $query->paginate($perPage);
    }
}
