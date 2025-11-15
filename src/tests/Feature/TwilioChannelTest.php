<?php

namespace Tests\Feature;

use App\Channels\TwilioChannel;
use App\Messages\TwilioMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification as LaravelNotification;
use Tests\TestCase;

/**
 * Test suite for Twilio channel credential guards.
 */
class TwilioChannelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Twilio channel gracefully handles missing account SID.
     */
    public function test_twilio_channel_handles_missing_account_sid(): void
    {
        // Simulate missing account SID
        config([
            'services.twilio.account_sid' => null,
            'services.twilio.auth_token' => 'test_token',
            'services.twilio.phone_number' => '+1234567890',
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return new TwilioMessage('+1987654321', 'Test message');
            }
        };

        // Should not throw exception
        $channel->send($notifiable, $notification);

        // Verification: if we get here without exception, the guard worked
        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel gracefully handles missing auth token.
     */
    public function test_twilio_channel_handles_missing_auth_token(): void
    {
        // Simulate missing auth token
        config([
            'services.twilio.account_sid' => 'test_sid',
            'services.twilio.auth_token' => null,
            'services.twilio.phone_number' => '+1234567890',
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return new TwilioMessage('+1987654321', 'Test message');
            }
        };

        // Should not throw exception
        $channel->send($notifiable, $notification);

        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel gracefully handles missing phone number.
     */
    public function test_twilio_channel_handles_missing_phone_number(): void
    {
        // Simulate missing phone number
        config([
            'services.twilio.account_sid' => 'test_sid',
            'services.twilio.auth_token' => 'test_token',
            'services.twilio.phone_number' => null,
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return new TwilioMessage('+1987654321', 'Test message');
            }
        };

        // Should not throw exception
        $channel->send($notifiable, $notification);

        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel handles notifications without toTwilio method.
     */
    public function test_twilio_channel_handles_missing_totwilio_method(): void
    {
        config([
            'services.twilio.account_sid' => 'test_sid',
            'services.twilio.auth_token' => 'test_token',
            'services.twilio.phone_number' => '+1234567890',
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;

        // Create notification without toTwilio method
        $notification = $this->createMock(LaravelNotification::class);

        // Should not throw exception
        $channel->send($notifiable, $notification);

        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel handles null message from toTwilio.
     */
    public function test_twilio_channel_handles_null_message(): void
    {
        config([
            'services.twilio.account_sid' => 'test_sid',
            'services.twilio.auth_token' => 'test_token',
            'services.twilio.phone_number' => '+1234567890',
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return null;  // Notification doesn't need SMS
            }
        };

        // Should not throw exception
        $channel->send($notifiable, $notification);

        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel handles message without phone number.
     */
    public function test_twilio_channel_handles_missing_phone_in_message(): void
    {
        config([
            'services.twilio.account_sid' => 'test_sid',
            'services.twilio.auth_token' => 'test_token',
            'services.twilio.phone_number' => '+1234567890',
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return new TwilioMessage('', 'Test message');  // Empty phone
            }
        };

        // Should not throw exception
        $channel->send($notifiable, $notification);

        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel does not initialize client until needed.
     */
    public function test_twilio_channel_lazy_initializes_client(): void
    {
        config([
            'services.twilio.account_sid' => null,
            'services.twilio.auth_token' => null,
            'services.twilio.phone_number' => null,
        ]);

        // Creating the channel should not fail (no constructor initialization)
        $channel = new TwilioChannel;

        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return new TwilioMessage('+1987654321', 'Test message');
            }
        };

        // Sending should gracefully skip due to missing credentials
        $channel->send($notifiable, $notification);

        // No error thrown, channel remains stable
        $this->assertTrue(true);
    }

    /**
     * Test that all credentials must be present for channel to work.
     */
    public function test_twilio_channel_requires_all_credentials(): void
    {
        // Test with only partial credentials (just SID and token, missing phone)
        config([
            'services.twilio.account_sid' => 'test_sid',
            'services.twilio.auth_token' => 'test_token',
            'services.twilio.phone_number' => '',  // Empty phone
        ]);

        $channel = new TwilioChannel;
        $notifiable = new AnonymousNotifiable;
        $notification = new class extends LaravelNotification
        {
            public function toTwilio($notifiable): ?TwilioMessage
            {
                return new TwilioMessage('+1987654321', 'Test message');
            }
        };

        // Should not throw exception
        $channel->send($notifiable, $notification);

        $this->assertTrue(true);
    }

    /**
     * Test that Twilio channel maintains stability across multiple send attempts with missing credentials.
     */
    public function test_twilio_channel_stable_with_repeated_send_attempts(): void
    {
        config([
            'services.twilio.account_sid' => null,
            'services.twilio.auth_token' => null,
            'services.twilio.phone_number' => null,
        ]);

        $channel = new TwilioChannel;

        // Simulate multiple send attempts (as in local dev or CI)
        for ($i = 0; $i < 5; $i++) {
            $notifiable = new AnonymousNotifiable;
            $notification = new class extends LaravelNotification
            {
                public function toTwilio($notifiable): ?TwilioMessage
                {
                    return new TwilioMessage('+1987654321', 'Test message');
                }
            };

            // Each send should complete without error
            $channel->send($notifiable, $notification);
        }

        // If we get here, channel remained stable through multiple attempts
        $this->assertTrue(true);
    }
}
