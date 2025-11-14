<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GiftType;
use App\Models\User;

class GiftTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view gift type list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GiftType $giftType): bool
    {
        return true; // Anyone can view gift type details
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GiftType $giftType): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GiftType $giftType): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
