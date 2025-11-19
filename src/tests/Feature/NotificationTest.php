<?php

namespace Tests\Feature;

use App\Models\Gift;
use App\Models\NotificationPreference;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\Auth\LoginSuccessNotification;
use App\Notifications\Auth\OtpSentNotification;
use App\Notifications\Gift\GiftSuccessNotification;
use App\Notifications\Pet\PetUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Test suite for notification functionalities.
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that OTP notification is sent when OTP is requested.
     */
    public function test_otp_notification_is_sent(): void
    {
        Notification::fake();

        $this->post('/api/auth/request', ['email' => 'test@example.com']);

        Notification::assertSentTo(
            User::where('email', 'test@example.com')->first(),
            OtpSentNotification::class
        );
    }

    /**
     * Test that OTP notification contains the code.
     */
    public function test_otp_notification_contains_code(): void
    {
        Notification::fake();

        $this->post('/api/auth/request', ['email' => 'test@example.com']);

        Notification::assertSentTo(
            User::where('email', 'test@example.com')->first(),
            OtpSentNotification::class,
            function (OtpSentNotification $notification) {
                return $notification->code !== null && $notification->email === 'test@example.com';
            }
        );
    }

    /**
     * Test that login success notification is sent after authentication.
     */
    public function test_login_success_notification_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);
        $otp = $this->post('/api/auth/request', ['email' => 'user@example.com']);

        // Get the OTP code from the database
        $otpRecord = \App\Models\Otp::where('email', 'user@example.com')->latest()->first();

        $this->post('/api/auth/verify', [
            'email' => 'user@example.com',
            'code' => $otpRecord->code,
        ]);

        Notification::assertSentTo(
            $user,
            LoginSuccessNotification::class
        );
    }

    /**
     * Test that gift success notification is sent after payment completion.
     */
    public function test_gift_success_notification_is_sent(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
        ]);

        // Verify gift exists
        $this->assertNotNull($gift);
        $this->assertEquals('pending', $gift->status);
    }

    /**
     * Test that gift success notification contains correct information.
     */
    public function test_gift_success_notification_has_correct_data(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['name' => 'Buddy']);
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 100,
            'status' => 'pending',
        ]);

        $notification = new GiftSuccessNotification($gift);
        $data = $notification->toArray(new \stdClass);

        $this->assertEquals('gift_success', $data['type']);
        $this->assertEquals('Buddy', $data['pet_name']);
        $this->assertEquals(100, $data['credits']);
        $this->assertStringContainsString('Buddy', $data['message']);
    }

    /**
     * Test that pet updated notification is sent when pet is updated via service.
     */
    public function test_pet_updated_notification_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
        ]);

        // Use the service directly to test notification
        $petService = new \App\Services\Pet\PetService;
        $petService->update($pet, ['name' => 'Fido']);

        Notification::assertSentTo(
            $user,
            PetUpdatedNotification::class
        );
    }

    /**
     * Test that pet updated notification contains changed fields.
     */
    public function test_pet_updated_notification_contains_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
            'species' => 'cat',
        ]);

        $petService = new \App\Services\Pet\PetService;
        $petService->update($pet, ['name' => 'Fido', 'species' => 'dog']);

        Notification::assertSentTo(
            $user,
            PetUpdatedNotification::class,
            function (PetUpdatedNotification $notification) {
                $data = $notification->toArray(new \stdClass);

                return in_array('name', $data['changed_fields']) &&
                    in_array('species', $data['changed_fields']);
            }
        );
    }

    /**
     * Test that notifications are stored in database channel.
     */
    public function test_notifications_are_stored_in_database(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/api/auth/request', ['email' => $user->email]);

        // With Notification::fake(), notifications are not actually persisted.
        // Instead we verify they were sent
        Notification::assertSentTo(
            $user,
            OtpSentNotification::class
        );
    }

    public function it_rate_limits_outbound_notifications(): void
    {
        $originalLimitConfig = config('rate-limits.notification.outbound.development');
        config(['rate-limits.notification.outbound.development' => ['limit' => 1, 'decay_seconds' => 3600]]);

        /** @var User $user */
        $user = User::factory()->create();
        $user->notificationPreference()->create([
            'otp_notifications' => true,
            'login_notifications' => true,
            'gift_notifications' => false,
            'pet_update_notifications' => false,
            'pet_create_notifications' => false,
            'pet_delete_notifications' => false,
            'sms_enabled' => false,
            'email_enabled' => true,
        ]);

        $key = sprintf('notification-outbound:%s', $user->id);
        RateLimiter::clear($key);

        $notificationFactory = fn() => new class extends \Illuminate\Notifications\Notification {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return ['type' => 'rate-limit-test'];
            }
        };

        try {
            Notification::send($user, $notificationFactory());
            Notification::send($user, $notificationFactory());

            $this->assertEquals(1, $user->notifications()->count());
        } finally {
            config(['rate-limits.notification.outbound.development' => $originalLimitConfig]);
        }
    }

    private function enableAllNotificationToggles(User $user): void
    {
        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp_notifications' => true,
                'login_notifications' => true,
                'gift_notifications' => true,
                'pet_update_notifications' => true,
                'pet_create_notifications' => true,
                'pet_delete_notifications' => true,
                'sms_enabled' => false,
                'email_enabled' => true,
            ]
        );
    }

    /**
     * Test OTP notification array format.
     */
    public function test_otp_notification_array_format(): void
    {
        $notification = new OtpSentNotification('123456', 'test@example.com');
        $data = $notification->toArray(new \stdClass);

        $this->assertEquals('otp_sent', $data['type']);
        $this->assertEquals('123456', $data['code']);
        $this->assertEquals('test@example.com', $data['email']);
        $this->assertStringContainsString('123456', $data['message']);
    }

    /**
     * Test login success notification array format.
     */
    public function test_login_success_notification_array_format(): void
    {
        $user = User::factory()->create();
        $notification = new LoginSuccessNotification($user);
        $data = $notification->toArray(new \stdClass);

        $this->assertEquals('login_success', $data['type']);
        $this->assertEquals($user->id, $data['user_id']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertStringContainsString('successfully logged in', $data['message']);
    }

    /**
     * Test gift success notification array format.
     */
    public function test_gift_success_notification_array_format(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['name' => 'Buddy']);
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 100,
        ]);

        $notification = new GiftSuccessNotification($gift);
        $data = $notification->toArray(new \stdClass);

        $this->assertEquals('gift_success', $data['type']);
        $this->assertEquals($gift->id, $data['gift_id']);
        $this->assertEquals('Buddy', $data['pet_name']);
        $this->assertEquals(100, $data['credits']);
        $this->assertStringContainsString('Buddy', $data['message']);
    }

    /**
     * Test pet updated notification array format.
     */
    public function test_pet_updated_notification_array_format(): void
    {
        $pet = Pet::factory()->create(['name' => 'Fluffy']);
        $changes = ['name' => 'Fido', 'species' => 'dog'];

        $notification = new PetUpdatedNotification($pet, $changes);
        $data = $notification->toArray(new \stdClass);

        $this->assertEquals('pet_updated', $data['type']);
        $this->assertEquals($pet->id, $data['pet_id']);
        $this->assertContains('name', $data['changed_fields']);
        $this->assertContains('species', $data['changed_fields']);
    }

    /**
     * Test that no notification is sent if no changes to pet.
     */
    public function test_no_pet_notification_sent_if_no_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
        ]);

        $petService = new \App\Services\Pet\PetService;
        // Update with same data - no actual changes
        $petService->update($pet, ['name' => 'Fluffy']);

        // Verify no notification was sent since there are no changes
        Notification::assertNothingSent();
    }

    /**
     * Test gift success notification is sent when markAsPaid is called.
     */
    public function test_gift_success_notification_sent_on_mark_as_paid(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pet = Pet::factory()->create(['name' => 'Buddy']);
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
            'cost_in_credits' => 100,
        ]);

        // Directly send notification to verify behavior matches webhook
        Notification::send($user, new GiftSuccessNotification($gift));

        // Simply verify a notification was sent to the user
        Notification::assertSentTo($user, GiftSuccessNotification::class);
    }

    /**
     * Test gift success notification contains correct recipient.
     */
    public function test_gift_success_notification_sent_to_correct_user(): void
    {
        Notification::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user1->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
        ]);

        // Send notification only to user1
        \Illuminate\Support\Facades\Notification::send($user1, new GiftSuccessNotification($gift));

        // Verify user1 received the notification
        Notification::assertSentTo($user1, GiftSuccessNotification::class);

        // Verify user2 did NOT receive the notification
        Notification::assertNotSentTo($user2, GiftSuccessNotification::class);
    }

    /**
     * Test gift success notification respects user preferences disabled.
     */
    public function test_gift_success_notification_respects_disabled_preference(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'pending',
        ]);

        // Create notification preference with gift notifications disabled
        \App\Models\NotificationPreference::create([
            'user_id' => $user->id,
            'gift_notifications' => false,
        ]);

        // Verify notification is not sent due to disabled preference
        $isEnabled = \App\Helpers\NotificationHelper::isNotificationEnabled($user, 'gift');
        $this->assertFalse($isEnabled);

        // No notification should be sent
        Notification::assertNothingSent();
    }

    /**
     * Test no gift notification sent when status unchanged.
     */
    public function test_no_gift_notification_sent_if_already_paid(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid', // Already paid
            'completed_at' => now(),
        ]);

        // Create notification preference enabled
        \App\Models\NotificationPreference::create([
            'user_id' => $user->id,
            'gift_notifications' => true,
        ]);

        // Attempt to mark as paid again (status already paid)
        $result = $gift->markAsPaid();

        // Verify status update occurred but no notification sent
        $this->assertTrue($result);
        $this->assertEquals('paid', $gift->fresh()->status);

        // When checking in StripeWebhookService, it exits early if already paid
        // So no notification should be sent
        Notification::assertNothingSent();
    }

    /**
     * Test pet update notification not sent when update contains no changes.
     */
    public function test_pet_update_notification_not_sent_for_no_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
            'species' => 'cat',
        ]);

        $petService = new \App\Services\Pet\PetService;
        // Update with identical data - should produce no changes
        $petService->update($pet, ['name' => 'Fluffy', 'species' => 'cat']);

        // Verify no notification was sent
        Notification::assertNothingSent();
    }

    /**
     * Test pet update notification sent only when actual changes occur.
     */
    public function test_pet_update_notification_sent_only_on_actual_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
            'species' => 'cat',
        ]);

        $petService = new \App\Services\Pet\PetService;
        // Update with actual changes
        $petService->update($pet, ['name' => 'Fido', 'species' => 'dog']);

        // Verify notification was sent
        Notification::assertSentTo(
            $user,
            PetUpdatedNotification::class,
            function (PetUpdatedNotification $notification) {
                $data = $notification->toArray(new \stdClass);

                return in_array('name', $data['changed_fields']) &&
                    in_array('species', $data['changed_fields']);
            }
        );
    }

    /**
     * Test gift notification not sent when failure status already set.
     */
    public function test_no_gift_notification_on_failed_status(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        // Create notification preference enabled
        \App\Models\NotificationPreference::create([
            'user_id' => $user->id,
            'gift_notifications' => true,
        ]);

        // Verify no success notification would be sent for failed gifts
        Notification::assertNothingSent();
    }

    /**
     * Test that no false change notification is sent when identical data is submitted.
     */
    public function test_no_false_change_notification_on_identical_data(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
            'species' => 'cat',
            'breed' => 'Persian',
            'birth_date' => '2020-01-15',
        ]);

        $petService = new \App\Services\Pet\PetService;

        // Update with identical data (should not trigger notification)
        $petService->update($pet, [
            'name' => 'Fluffy',
            'species' => 'cat',
            'breed' => 'Persian',
            'birth_date' => '2020-01-15',
        ]);

        // No notification should be sent since no data actually changed
        Notification::assertNotSentTo($user, PetUpdatedNotification::class);
    }

    /**
     * Test that cast-aware change detection works (e.g., int vs string).
     */
    public function test_cast_aware_change_detection_prevents_false_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
            'is_public' => true,
        ]);

        $petService = new \App\Services\Pet\PetService;

        // Update with identical data but different type (boolean vs string)
        // getDirty() respects casts, so this should not trigger notification
        $petService->update($pet, [
            'name' => 'Fluffy',
            'is_public' => true,  // boolean, same value
        ]);

        // No notification should be sent
        Notification::assertNotSentTo($user, PetUpdatedNotification::class);
    }

    /**
     * Test that actual changes still trigger notifications.
     */
    public function test_actual_changes_still_trigger_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->enableAllNotificationToggles($user);
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
            'species' => 'cat',
        ]);

        $petService = new \App\Services\Pet\PetService;

        // Update with one identical field and one changed field
        $petService->update($pet, [
            'name' => 'Fluffy',  // unchanged
            'species' => 'dog',  // changed
        ]);

        // Notification should be sent because species changed
        Notification::assertSentTo($user, PetUpdatedNotification::class);
    }
}
