<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

/**
 * Twilio SMS notification channel for sending SMS messages via Twilio.
 */
class TwilioChannel
{
    /**
     * The Twilio API client.
     */
    protected Client $client;

    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');

        $this->client = new Client($accountSid, $authToken);
    }

    /**
     * Send the given notification.
     *
     * @throws TwilioException
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTwilio')) {
            return;
        }

        /** @var mixed $notification */
        $message = $notification->toTwilio($notifiable);

        if (! $message || ! $message->getPhoneNumber()) {
            return;
        }

        $this->client->messages->create(
            $message->getPhoneNumber(),
            [
                'from' => config('services.twilio.phone_number'),
                'body' => $message->getContent(),
            ]
        );
    }
}
