<?php

namespace App\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Custom Laravel notification channel for handling SNS-based SMS delivery.
 */
class SmsChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(protected SmsService $smsService) {}

    /**
     * Send the notification via the SMS service.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (! $message) {
            return;
        }

        $phoneNumber = $this->getPhoneNumber($notifiable);

        try {
            $this->smsService->send($message, $phoneNumber);
        } catch (Throwable $exception) {
            Log::error('Failed to send SMS notification', [
                'notification' => $notification::class,
                'notifiable' => is_object($notifiable) ? $notifiable::class : gettype($notifiable),
                'phone_number' => $phoneNumber,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Resolve the SMS routing phone number from the notifiable model.
     */
    protected function getPhoneNumber(mixed $notifiable): ?string
    {
        if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationForSms')) {
            return $notifiable->routeNotificationForSms();
        }

        return null;
    }
}
