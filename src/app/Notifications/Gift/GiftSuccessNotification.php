<?php

namespace App\Notifications\Gift;

use App\Helpers\NotificationHelper;
use App\Messages\TwilioMessage;
use App\Models\Gift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a gift is successfully sent.
 */
class GiftSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Gift $gift,
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
        $petName = $this->gift->pet->name;
        $credits = $this->gift->cost_in_credits;

        return (new MailMessage)
            ->markdown('emails.gift_success', [
                'petName' => $petName,
                'credits' => $credits,
                'giftId' => $this->gift->id,
                'date' => $this->gift->completed_at?->format('M d, Y H:i:s') ?? 'Pending',
            ])
            ->subject(__('gifts.created.email.subject'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $credits = $this->gift->cost_in_credits;

        return [
            'type' => 'gift_success',
            'gift_id' => $this->gift->id,
            'pet_id' => $this->gift->pet_id,
            'credits' => $credits,
            'pet_name' => $this->gift->pet->name,
            'message' => __('gifts.created.email.intro', [
                'credits' => $credits,
                'pet_name' => $this->gift->pet->name,
            ]),
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        $credits = $this->gift->cost_in_credits;

        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            __('gifts.created.sms.body', [
                'credits' => $credits,
                'pet_name' => $this->gift->pet->name,
                'gift_id' => $this->gift->id,
            ])
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.gift_success';
    }
}
