<?php

namespace App\Services\Auth\Notifications;

use App\Exceptions\Auth\InvalidUserException;
use App\Models\User;
use App\Models\NotificationPreference;
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
                'donation_notifications' => true,
                'pet_update_notifications' => true,
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

        // Update the specific preference
        if (in_array($type, [
            'otp_notifications',
            'login_notifications',
            'donation_notifications',
            'pet_update_notifications',
            'sms_enabled',
            'email_enabled',
        ])) {
            $preferences->$type = $enabled;
            $preferences->save();
        } else {
            throw new \InvalidArgumentException(NotificationsMessages::notificationPreferenceNotFound());
        }

        return $preferences;
    }
}
