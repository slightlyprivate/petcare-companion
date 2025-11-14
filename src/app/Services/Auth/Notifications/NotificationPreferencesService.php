<?php

namespace App\Services\Auth\Notifications;

use App\Exceptions\Auth\InvalidUserException;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Support\Messages\NotificationsMessages;

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
    public function getUserPreferences(int $userId): NotificationPreference
    {
        $user = User::find($userId);
        if (! $user) {
            throw new InvalidUserException("User with ID {$userId} not found.");
        }

        // Retrieve existing preferences or create default ones
        $preferences = $user->notificationPreference;

        if (! $preferences) {
            // Create default preferences if they don't exist
            $preferences = NotificationPreference::create([
                'user_id' => $user->id,
                'otp_notifications' => true,
                'login_notifications' => true,
                'gift_notifications' => true,
                'pet_update_notifications' => true,
                'pet_create_notifications' => true,
                'pet_delete_notifications' => true,
                'sms_enabled' => true,
                'email_enabled' => true,
            ]);
        }

        return $preferences;
    }

    /**
     * Update a specific notification preference for a user.
     */
    public function updateUserPreference(int $userId, string $type, bool $enabled): NotificationPreference
    {
        $user = User::find($userId);
        if (! $user) {
            throw new InvalidUserException("User with ID {$userId} not found.");
        }

        $preferences = $this->getUserPreferences($userId);

        // Map user-friendly type names to database column names
        $typeMapping = [
            'otp' => 'otp_notifications',
            'login' => 'login_notifications',
            'gift' => 'gift_notifications',
            'pet_update' => 'pet_update_notifications',
            'pet_create' => 'pet_create_notifications',
            'pet_delete' => 'pet_delete_notifications',
            'sms' => 'sms_enabled',
            'email' => 'email_enabled',
        ];

        $columnName = $typeMapping[$type] ?? null;

        if (! $columnName || ! in_array($columnName, [
            'otp_notifications',
            'login_notifications',
            'gift_notifications',
            'pet_update_notifications',
            'pet_create_notifications',
            'pet_delete_notifications',
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
