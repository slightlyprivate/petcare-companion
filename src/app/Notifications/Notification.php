<?php

namespace App\Notifications;

use App\Messages\TwilioMessage;
use Illuminate\Notifications\Notification as BaseNotification;

/**
 * Base Notification class extended by all notifications.
 */
class Notification extends BaseNotification
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
