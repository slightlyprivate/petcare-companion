<?php

namespace App\Helpers;

use App\Models\NotificationPreference;
use App\Models\User;

/**
 * Helper for managing notification preferences and sending conditional notifications.
 */
class NotificationHelper
{
    /**
     * Check if a user has a specific notification type enabled.
     *
     * @param  string  $notificationType  One of: otp, login, donation, pet_update
     */
    public static function isNotificationEnabled(?User $user, string $notificationType): bool
    {
        if (! $user) {
            return false;
        }

        // Get or create preferences (defaults to true)
        $preferences = $user->notificationPreference ?? NotificationPreference::create([
            'user_id' => $user->id,
        ]);

        return $preferences->isNotificationEnabled($notificationType);
    }

    /**
     * Check if a user has a specific channel enabled.
     *
     * @param  string  $channel  One of: sms, email
     */
    public static function isChannelEnabled(?User $user, string $channel): bool
    {
        if (! $user) {
            return false;
        }

        // Get or create preferences (defaults to true)
        $preferences = $user->notificationPreference ?? NotificationPreference::create([
            'user_id' => $user->id,
        ]);

        return $preferences->isChannelEnabled($channel);
    }
}
