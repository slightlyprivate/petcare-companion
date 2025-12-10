<?php

namespace App\Services\Auth\Notifications;

use App\Exceptions\Auth\InvalidUserException;
use App\Models\NotificationPreference;
use App\Models\User;

/**
 * Service for managing user notification preferences.
 *
 * @group Authentication
 */
class NotificationPreferencesService
{
    /**
     * Get notification preferences for a user.
     */
    public function getUserPreferences(string $userId): NotificationPreference
    {
        $user = User::find($userId);
        if (! $user) {
            throw new InvalidUserException("User with ID {$userId} not found.");
        }

        // Retrieve existing preferences or create default ones
        $preferences = $user->notificationPreferences;

        if (! $preferences) {
            $notificationDefaults = config('notifications.defaults.notifications', []);
            $channelDefaults = config('notifications.defaults.channels', []);

            $preferences = $user->notificationPreferences()->create([
                'otp_notifications' => $notificationDefaults['otp'] ?? true,
                'login_notifications' => $notificationDefaults['login'] ?? true,
                'gift_notifications' => $notificationDefaults['gift'] ?? true,
                'pet_update_notifications' => $notificationDefaults['pet_update'] ?? true,
                'pet_create_notifications' => $notificationDefaults['pet_create'] ?? true,
                'pet_delete_notifications' => $notificationDefaults['pet_delete'] ?? true,
                'pet_activity' => $notificationDefaults['pet_activity'] ?? false,
                'appointment_created' => $notificationDefaults['appointment_created'] ?? false,
                'caregiver_invitation' => $notificationDefaults['caregiver_invitation'] ?? false,
                'gift_received' => $notificationDefaults['gift_received'] ?? false,
                'routine_reminder' => $notificationDefaults['routine_reminder'] ?? false,
                'sms_enabled' => $channelDefaults['sms'] ?? true,
                'email_enabled' => $channelDefaults['email'] ?? true,
            ]);
        }

        return $preferences;
    }

    /**
     * Update a specific notification preference for a user.
     */
    public function updateUserPreference(string $userId, string $type, bool $enabled): NotificationPreference
    {
        $user = User::find($userId);
        if (! $user) {
            throw new InvalidUserException("User with ID {$userId} not found.");
        }

        $preferences = $this->getUserPreferences($userId);

        // Map user-friendly type names to database column names
        $typeMapping = [
            'pet_activity' => 'pet_activity',
            'appointment_created' => 'appointment_created',
            'caregiver_invitation' => 'caregiver_invitation',
            'gift_received' => 'gift_received',
            'routine_reminder' => 'routine_reminder',
            'sms' => 'sms_enabled',
            'email' => 'email_enabled',
        ];

        $columnName = $typeMapping[$type] ?? null;

        if (! $columnName || ! in_array($columnName, [
            'pet_activity',
            'appointment_created',
            'caregiver_invitation',
            'gift_received',
            'routine_reminder',
            'sms_enabled',
            'email_enabled',
        ])) {
            throw new \InvalidArgumentException(__('notifications.update.errors.not_found'));
        }

        $preferences->$columnName = $enabled;
        $preferences->save();

        return $preferences;
    }
}
