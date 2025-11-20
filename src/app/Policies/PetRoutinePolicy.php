<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\PetRoutine;
use App\Models\User;

/**
 * Policy governing access to pet routines.
 * Owners manage routines; caregivers can view and complete.
 *
 * @group Policies
 */
class PetRoutinePolicy
{
    /**
     * Determine whether the user can list routines for a pet.
     */
    public function viewAny(User $user, Pet $pet): bool
    {
        return $this->isOwner($user, $pet) || $this->isCaregiver($user, $pet) || $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific routine.
     */
    public function view(User $user, PetRoutine $routine): bool
    {
        return $this->viewAny($user, $routine->pet);
    }

    /**
     * Determine whether the user can create routines for the pet (owners only).
     */
    public function create(User $user, Pet $pet): bool
    {
        return $this->isOwner($user, $pet) || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the routine (owners only).
     */
    public function update(User $user, PetRoutine $routine): bool
    {
        return $this->isOwner($user, $routine->pet) || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the routine (owners only).
     */
    public function delete(User $user, PetRoutine $routine): bool
    {
        return $this->isOwner($user, $routine->pet) || $user->isAdmin();
    }

    /**
     * Determine whether the user can mark occurrences as complete (owners & caregivers).
     */
    public function complete(User $user, PetRoutine $routine): bool
    {
        return $this->viewAny($user, $routine->pet);
    }

    /**
     * Helper: check owner role.
     */
    protected function isOwner(User $user, Pet $pet): bool
    {
        if ($user->id === $pet->user_id) {
            return true;
        }

        return $pet->petUsers()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }

    /**
     * Helper: check caregiver role.
     */
    protected function isCaregiver(User $user, Pet $pet): bool
    {
        return $pet->petUsers()
            ->where('user_id', $user->id)
            ->where('role', 'caregiver')
            ->exists();
    }
}
