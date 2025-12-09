<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification used for verifying the SMS channel configuration.
 */
class TestSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Determine the channels the notification should be sent through.
     *
     * @return array<int, class-string>
     */
    public function via(mixed $notifiable): array
    {
        return [SmsChannel::class];
    }

    /**
     * Get the SMS payload for the notification.
     */
    public function toSms(mixed $notifiable): string
    {
        return 'PetCare Companion SMS integration is configured.';
    }
}
