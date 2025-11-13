<?php

namespace App\Support\Messages;

/**
 * Centralized messages for the application.
 */
class NotificationsMessages
{
    /**
     * Message indicating successful update of notification preferences.
     */
    public static function notificationPreferenceUpdated(): string
    {
        return __('notifications.preferences.update.success');
    }

    /**
     * Message indicating failure to update notification preferences.
     */
    public static function notificationPreferenceUpdateFailed(): string
    {
        return __('notifications.preferences.update.failure');
    }

    /**
     * Message indicating notification preference not found.
     */
    public static function notificationPreferenceNotFound(): string
    {
        return __('notifications.preferences.update.not_found');
    }

    /**
     * Message indicating all notifications have been disabled successfully.
     */
    public static function notificationsDisabledSuccessfully(): string
    {
        return __('notifications.preferences.disable_all.success');
    }

    /**
     * Message indicating all notifications have been enabled successfully.
     */
    public static function notificationsEnabledSuccessfully(): string
    {
        return __('notifications.preferences.enable_all.success');
    }
}
