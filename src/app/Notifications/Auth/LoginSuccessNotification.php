<?php

namespace App\Notifications\Auth;

use App\Helpers\NotificationHelper;
use App\Messages\TwilioMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Notification sent when user successfully authenticates.
 */
class LoginSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $user,
        public ?string $ipAddress = null,
        public ?string $device = null,
        public ?Carbon $loggedInAt = null,
    ) {
        $this->loggedInAt = $loggedInAt ?? now();
        $this->ipAddress = $ipAddress ?? request()->ip();
        $this->device = $device ?? $this->normalizeUserAgent(request()->userAgent());
    }

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
            ->view('emails.login_success', [
                'time' => $this->loggedInAt?->setTimezone(config('app.timezone'))->format('M d, Y H:i:s T'),
                'ipAddress' => $this->ipAddress,
                'device' => $this->device,
            ])
            ->subject(__('auth.login.email.subject'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'login_success',
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'message' => __('auth.login.email.intro', [
                'time' => $this->loggedInAt?->format('M d, Y H:i:s'),
            ]),
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            __('auth.login.sms.body', [
                'time' => $this->loggedInAt?->format('H:i'),
            ])
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.login_success';
    }

    private function normalizeUserAgent(?string $userAgent): ?string
    {
        return $userAgent ? trim($userAgent) : null;
    }
}
