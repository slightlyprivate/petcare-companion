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

        return $user->id === $pet->user_id;
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
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pet $pet): bool
    {
        return false;
    }
}
