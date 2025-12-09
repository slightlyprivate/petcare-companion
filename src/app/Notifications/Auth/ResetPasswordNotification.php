<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public ?string $email = null,
        public ?string $resetUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresIn = $this->getPasswordExpireMinutes();

        return (new MailMessage)
            ->subject(Lang::get('Reset Your Password'))
            ->view('emails.password-reset', [
                'resetUrl' => $this->resetUrl ?? $this->buildResetUrl($notifiable),
                'expiresIn' => $expiresIn,
            ]);
    }

    protected function buildResetUrl(object $notifiable): string
    {
        $email = $this->email ?? $notifiable->getEmailForPasswordReset();
        $baseUrl = rtrim(config('app.frontend_url') ?? config('app.url'), '/');

        return $baseUrl.'/reset-password?token='.$this->token.'&email='.urlencode($email);
    }

    protected function getPasswordExpireMinutes(): int
    {
        $passwordBroker = config('auth.defaults.passwords');

        return (int) config("auth.passwords.{$passwordBroker}.expire", 60);
    }
}
