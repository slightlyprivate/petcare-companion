<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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
    protected ?Client $client = null;

    /**
     * Send the given notification.
     *
     * @throws TwilioException
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        // Guard: Check if notification has Twilio support
        if (! method_exists($notification, 'toTwilio')) {
            return;
        }

        // Guard: Check if credentials are available
        if (! $this->hasCredentials()) {
            Log::warning('Twilio credentials are not configured. Skipping SMS notification.');

            return;
        }

        /** @var mixed $notification */
        $message = $notification->toTwilio($notifiable);

        if (! $message || ! $message->getPhoneNumber()) {
            return;
        }

        try {
            $this->getClient()->messages->create(
                $message->getPhoneNumber(),
                [
                    'from' => config('services.twilio.phone_number'),
                    'body' => $message->getContent(),
                ]
            );
        } catch (TwilioException $e) {
            Log::error('Failed to send SMS via Twilio', [
                'error' => $e->getMessage(),
                'phone_number' => $message->getPhoneNumber(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if Twilio credentials are configured.
     */
    protected function hasCredentials(): bool
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $phoneNumber = config('services.twilio.phone_number');

        return ! empty($accountSid) && ! empty($authToken) && ! empty($phoneNumber);
    }

    /**
     * Lazily initialize and return the Twilio client.
     */
    protected function getClient(): Client
    {
        if ($this->client === null) {
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');

            $this->client = new Client($accountSid, $authToken);
        }

        return $this->client;
    }
}
