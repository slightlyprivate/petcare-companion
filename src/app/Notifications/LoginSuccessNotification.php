<?php

namespace App\Notifications;

use App\Messages\TwilioMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'twilio'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->markdown('emails.login_success', [
                'email' => $this->user->email,
                'time' => now()->format('M d, Y H:i:s'),
            ])
            ->subject('Successful Login to PetCare Companion');
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
            'message' => 'You have successfully logged in to PetCare Companion at '.now()->format('M d, Y H:i:s'),
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            'Welcome back to PetCare Companion! You successfully logged in at '.now()->format('H:i').". If this wasn't you, contact support."
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.login_success';
    }
}
