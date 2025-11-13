<?php

namespace App\Notifications;

use App\Messages\TwilioMessage;
use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a donation is successfully processed.
 */
class DonationSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Donation $donation,
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
        $petName = $this->donation->pet->name;
        $amount = $this->donation->amount_cents / 100;

        return (new MailMessage)
            ->markdown('emails.donation_success', [
                'petName' => $petName,
                'amount' => $amount,
                'donationId' => $this->donation->id,
                'date' => $this->donation->completed_at?->format('M d, Y H:i:s') ?? 'Pending',
            ])
            ->subject('Thank You for Your Donation');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $amount = $this->donation->amount_cents / 100;

        return [
            'type' => 'donation_success',
            'donation_id' => $this->donation->id,
            'pet_id' => $this->donation->pet_id,
            'amount' => $amount,
            'pet_name' => $this->donation->pet->name,
            'message' => "Thank you for your \${$amount} donation to {$this->donation->pet->name}!",
        ];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): ?TwilioMessage
    {
        $amount = $this->donation->amount_cents / 100;

        return new TwilioMessage(
            $notifiable->phone_number ?? '',
            "Thank you for your \${$amount} donation to {$this->donation->pet->name}! Your donation ID: {$this->donation->id}"
        );
    }

    /**
     * Get the markdown representation of the notification.
     */
    public function toMarkdown(object $notifiable): string
    {
        return 'emails.donation_success';
    }
}
