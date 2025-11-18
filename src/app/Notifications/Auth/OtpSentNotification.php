<?php

namespace App\Notifications\Auth;

use App\Helpers\NotificationHelper;
use App\Messages\TwilioMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when OTP is requested for authentication.
 */
class OtpSentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $code,
        public string $email,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (NotificationHelper::isChannelEnabled($notifiable, 'email')) {
            $channels[] = 'mail';
        }

        if (NotificationHelper::isChannelEnabled($notifiable, 'sms')) {
            $channels[] = 'twilio';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->markdown('emails.otp_sent', [
                'code' => $this->code,
            ])
            ->subject(__('auth.otp.email.subject'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'otp_sent',
            'code' => $this->code,
            'email' => $this->email,
            'message' => __('auth.otp.email.intro', ['code' => $this->code]),
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            __('auth.otp.sms.body', ['code' => $this->code])
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.otp_sent';
    }
}
