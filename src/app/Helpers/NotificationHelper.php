<?php

namespace App\Helpers;

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

        $key = self::normalizeNotificationKey($notificationType);

        // Get preferences if they exist, otherwise default to true
        $preferences = $user->notificationPreference;

        if (! $preferences) {
            $defaults = config('notifications.defaults.notifications', []);

            return (bool) ($defaults[$key] ?? false);
        }

        return $preferences->isNotificationEnabled($key);
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
            $channelDefaults = config('notifications.defaults.channels', []);

            return (bool) ($channelDefaults[$channel] ?? false);
        }

        return $preferences->isChannelEnabled($channel);
    }

    /**
     * Normalize friendly notification keys to their base identifiers.
     */
    private static function normalizeNotificationKey(string $notificationType): string
    {
        return match ($notificationType) {
            'otp_notifications' => 'otp',
            'login_notifications' => 'login',
            'gift_notifications' => 'gift',
            'gift_send_notifications' => 'gift_send',
            'pet_update_notifications' => 'pet_update',
            'pet_create_notifications' => 'pet_create',
            'pet_delete_notifications' => 'pet_delete',
            'otp', 'login', 'gift', 'gift_send', 'pet_update', 'pet_create', 'pet_delete' => $notificationType,
            default => $notificationType,
        };
    }
}
