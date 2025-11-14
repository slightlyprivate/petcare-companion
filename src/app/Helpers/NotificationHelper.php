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
     * @param  string  $notificationType  The type of notification to check (e.g., otp_notifications)
     */
    public static function isNotificationEnabled(?User $user, string $notificationType): bool
    {
        if (! $user) {
            return false;
        }

        // Get preferences if they exist, otherwise default to true
        $preferences = $user->notificationPreference;

        if (! $preferences) {
            // Default to enabled for all notification types if no preference exists yet
            return true;
        }

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

        // Get preferences if they exist, otherwise default to true
        $preferences = $user->notificationPreference;

        if (! $preferences) {
            // Default to enabled for all channels if no preference exists yet
            return true;
        }

        return $preferences->isChannelEnabled($channel);
    }
}
