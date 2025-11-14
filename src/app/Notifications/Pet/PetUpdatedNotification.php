<?php

namespace App\Notifications\Pet;

use App\Messages\TwilioMessage;
use App\Models\Pet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a pet is updated.
 */
class PetUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Pet $pet,
        public array $changes,
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
        $changedFields = array_keys($this->changes);

        return (new MailMessage)
            ->markdown('emails.pet_updated', [
                'petName' => $this->pet->name,
                'species' => $this->pet->species,
                'breed' => $this->pet->breed ?? 'Not specified',
                'status' => $this->pet->is_public ? 'Public' : 'Private',
                'changedFields' => implode(', ', $changedFields),
            ])
            ->subject(__('pet.notify.updated.subject', ['pet_name' => $this->pet->name]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $changedFields = array_keys($this->changes);

        return [
            'type' => 'pet_updated',
            'pet_id' => $this->pet->id,
            'pet_name' => $this->pet->name,
            'changed_fields' => $changedFields,
            'message' => __('pet.notify.updated.message', ['pet_name' => $this->pet->name])
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        $changedFields = array_keys($this->changes);

        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            __('pet.notify.updated.sms', ['pet_name' => $this->pet->name])
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.pet_updated';
    }
}
