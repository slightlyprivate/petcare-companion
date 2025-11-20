<?php

namespace App\Policies;

use App\Models\PetCaregiverInvitation;
use App\Models\User;

/**
 * Policy for managing pet caregiver invitation access control.
 *
 * @group Policies
 */
class PetCaregiverInvitationPolicy
{
    /**
     * Determine whether the user can delete (revoke) the invitation.
     *
     * Only the inviter can revoke an invitation.
     */
    public function delete(User $user, PetCaregiverInvitation $petCaregiverInvitation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $petCaregiverInvitation->inviter_id;
    }
}
