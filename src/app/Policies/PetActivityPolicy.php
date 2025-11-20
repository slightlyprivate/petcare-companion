<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;

/**
 * Policy controlling authorization for pet activities.
 * Caregivers and owners may create activities; only owners may delete.
 *
 * @group Policies
 */
class PetActivityPolicy
{
    /**
     * Determine whether the user can create an activity for the given pet.
     */
    public function create(User $user, Pet $pet): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Any associated user (owner or caregiver) may create activities.
        return $pet->petUsers()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the specified activity.
     * Only owners (or admins) may delete activities.
     */
    public function delete(User $user, PetActivity $activity): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $pet = $activity->pet;
        if (! $pet) {
            return false;
        }

        // Owner is either the pet's direct user_id or an owner role in pivot.
        if ($user->id === $pet->user_id) {
            return true;
        }

        return $pet->petUsers()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
