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
     * Determine if the given credit purchase can be viewed by the user.
     */
    public function view(User $user, CreditPurchase $creditPurchase): bool
    {
        return $user->id === $creditPurchase->user_id;
    }
}
