<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification as BaseNotification;

/**
 * Base Notification class extended by all notifications.
 */
class Notification extends BaseNotification
{
    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(mixed $notifiable): ?string
    {
        return null;
    }
}
