<?php

namespace App\Notifications\Pet;

use App\Messages\TwilioMessage;
use App\Models\Pet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a pet is deleted.
 */
class PetDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Pet $pet) {}

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
            ->markdown('emails.pet_deleted', [
                'petName' => $this->pet->name,
                'species' => $this->pet->species,
                'breed' => $this->pet->breed ?? 'Not specified',
                'ownerName' => $this->pet->owner_name,
            ])
            ->subject(__('pets.deleted.email.subject', ['pet_name' => $this->pet->name]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pet_deleted',
            'pet_id' => $this->pet->id,
            'pet_name' => $this->pet->name,
            'species' => $this->pet->species,
            'message' => __('pets.deleted.email.intro', ['pet_name' => $this->pet->name]),
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            __('pets.deleted.sms.body', ['pet_name' => $this->pet->name])
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.pet_deleted';
    }
}
