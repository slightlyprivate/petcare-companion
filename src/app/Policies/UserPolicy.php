<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy for managing user access control.
 *
 * @group Policies
 */
class UserPolicy
{
    /**
     * Determine whether the authenticated user can view any user models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the authenticated user can view a given user model.
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->isAdmin() || $user->is($targetUser);
    }

    /**
     * Determine whether the authenticated user can update a given user model.
     */
    public function update(User $user, User $targetUser): bool
    {
        return $user->isAdmin() || $user->is($targetUser);
    }

    /**
     * Determine whether the authenticated user can delete a given user model.
     */
    public function delete(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && ! $user->is($targetUser);
    }

    /**
     * Determine whether the authenticated user can restore a given user model.
     */
    public function restore(User $user, User $targetUser): bool
    {
        return false;
    }

    /**
     * Determine whether the authenticated user can permanently delete a given user model.
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        return false;
    }
}
