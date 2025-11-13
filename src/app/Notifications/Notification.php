<?php

namespace App\Notifications;

use App\Messages\TwilioMessage;

/**
 * Base Notification class extended by all notifications.
 */
class Notification implements \Illuminate\Notifications\Notification
{
    /**
     * Get the Twilio representation of the notification.
     */
    public function toTwilio(mixed $notifiable): ?TwilioMessage
    {
        // Customize the Twilio message for the notification
        return new TwilioMessage(
            phoneNumber: $notifiable->phone_number,
            content: 'Your notification content here'
        );
    }
}
