<?php

namespace App\Policies;

use App\Models\CreditPurchase;
use App\Models\User;

/**
 * Policy class for managing access to credit purchases.
 */
class CreditPurchasePolicy
{
    /**
     * Determine whether the user can view any credit purchases.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own purchases
    }

    /**
     * Determine if the given credit purchase can be viewed by the user.
     */
    public function view(User $user, CreditPurchase $creditPurchase): bool
    {
        return $user->id === $creditPurchase->user_id;
    }

    /**
     * Determine whether the user can create a credit purchase.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
