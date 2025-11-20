<?php

namespace App\Policies;

use App\Models\Gift;
use App\Models\User;

/**
 * Policy for managing gift access control.
 */
class GiftPolicy
{
    /**
     * Determine whether the user can view any gift models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific gift model.
     */
    public function view(User $user, Gift $gift): bool
    {
        return $user->isAdmin() || $gift->user_id === $user->id;
    }

    /**
     * Determine whether the user can create a gift.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the gift.
     */
    public function update(User $user, Gift $gift): bool
    {
        return $user->isAdmin() || $gift->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the gift.
     */
    public function delete(User $user, Gift $gift): bool
    {
        return $user->isAdmin() || $gift->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the gift.
     */
    public function restore(User $user, Gift $gift): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the gift.
     */
    public function forceDelete(User $user, Gift $gift): bool
    {
        return false;
    }
}
