<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Policy for managing appointment access control.
 */
class AppointmentPolicy
{
    /**
     * Determine whether the user can view any appointments.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own scoped list
    }

    /**
     * Determine whether the user can view the appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $appointment->pet && $appointment->pet->user_id === $user->id;
    }

    /**
     * Determine whether the user can create appointments.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create appointments for their pets
    }

    /**
     * Determine whether the user can update the appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $appointment->pet && $appointment->pet->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the appointment.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $appointment->pet && $appointment->pet->user_id === $user->id;
    }
}
