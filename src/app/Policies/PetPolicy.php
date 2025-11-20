<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;

/**
 * Policy for managing pet access control.
 *
 * @group Policies
 */
class PetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Allow access to index route; query must be scoped
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pet $pet): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can view
        if ($user->id === $pet->user_id) {
            return true;
        }

        // Caregivers can also view
        return $pet->petUsers()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create pets
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pet $pet): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $pet->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pet $pet): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $pet->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pet $pet): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $pet->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pet $pet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can invite caregivers for the pet.
     *
     * Only pet owners can send caregiver invitations.
     */
    public function inviteCaregiver(User $user, Pet $pet): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user is the owner (via user_id or pet_user pivot with owner role)
        if ($user->id === $pet->user_id) {
            return true;
        }

        // Check if user has owner role in pet_user pivot table
        return $pet->petUsers()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
