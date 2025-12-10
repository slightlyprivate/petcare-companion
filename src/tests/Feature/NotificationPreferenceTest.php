<?php

namespace Tests\Feature;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting user notification preferences.
     */
    public function test_user_can_get_notification_preferences(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $preferences = $user->notificationPreferences;
        $preferences->update([
            'pet_activity' => true,
            'gift_received' => true,
            'appointment_created' => false,
            'caregiver_invitation' => false,
            'routine_reminder' => false,
            'sms_enabled' => true,
            'email_enabled' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user/notification-preferences');

        $response->assertOk()
            ->assertJsonPath('data.pet_activity', true)
            ->assertJsonPath('data.gift_received', true)
            ->assertJsonPath('data.appointment_created', false)
            ->assertJsonPath('data.caregiver_invitation', false)
            ->assertJsonPath('data.routine_reminder', false)
            ->assertJsonPath('data.sms_enabled', true)
            ->assertJsonPath('data.email_enabled', false);
    }

    /**
     * Test creating default notification preferences if not exist.
     */
    public function test_default_preferences_created_if_not_exist(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user/notification-preferences');

        $response->assertOk()
            ->assertJsonPath('data.pet_activity', false)
            ->assertJsonPath('data.gift_received', false)
            ->assertJsonPath('data.appointment_created', false)
            ->assertJsonPath('data.caregiver_invitation', false)
            ->assertJsonPath('data.routine_reminder', false)
            ->assertJsonPath('data.sms_enabled', false)
            ->assertJsonPath('data.email_enabled', true);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test updating a specific notification preference.
     */
    public function test_user_can_update_notification_preference(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'pet_activity',
            'enabled' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'pet_activity')
            ->assertJsonPath('data.enabled', false);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'pet_activity' => false,
        ]);
    }

    /**
     * Test disabling all notifications.
     */
    public function test_user_can_disable_all_notifications(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $preferences = $user->notificationPreferences;
        $preferences->update([
            'pet_activity' => true,
            'appointment_created' => true,
            'caregiver_invitation' => true,
            'gift_received' => true,
            'routine_reminder' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/notification-preferences/disable-all');

        $response->assertOk()
            ->assertJsonPath('message', 'All notifications have been disabled.');

        $preferences->refresh();
        $this->assertFalse($preferences->pet_activity);
        $this->assertFalse($preferences->appointment_created);
        $this->assertFalse($preferences->caregiver_invitation);
        $this->assertFalse($preferences->gift_received);
        $this->assertFalse($preferences->routine_reminder);
    }

    /**
     * Test enabling all notifications.
     */
    public function test_user_can_enable_all_notifications(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $preferences = $user->notificationPreferences;
        $preferences->update([
            'pet_activity' => false,
            'appointment_created' => false,
            'caregiver_invitation' => false,
            'gift_received' => false,
            'routine_reminder' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/notification-preferences/enable-all');

        $response->assertOk()
            ->assertJsonPath('message', 'All notifications have been enabled.');

        $preferences->refresh();
        $this->assertTrue($preferences->pet_activity);
        $this->assertTrue($preferences->appointment_created);
        $this->assertTrue($preferences->caregiver_invitation);
        $this->assertTrue($preferences->gift_received);
        $this->assertTrue($preferences->routine_reminder);
    }

    /**
     * Test notification preference validation.
     */
    public function test_notification_preference_update_validation(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'invalid_type',
            'enabled' => true,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    /**
     * Test notification preference requires authentication.
     */
    public function test_notification_preferences_require_authentication(): void
    {
        $response = $this->getJson('/api/user/notification-preferences');

        $response->assertUnauthorized();
    }

    /**
     * Test preference checking method on model.
     */
    public function test_preference_checking_methods(): void
    {
        $user = User::factory()->create();
        $preferences = $user->notificationPreferences;
        $preferences->update([
            'pet_activity' => true,
            'gift_received' => false,
            'sms_enabled' => true,
            'email_enabled' => false,
        ]);

        $this->assertTrue($preferences->isNotificationEnabled('pet_activity'));
        $this->assertFalse($preferences->isNotificationEnabled('gift_received'));
        $this->assertTrue($preferences->isNotificationEnabled('otp'));
        $this->assertTrue($preferences->isChannelEnabled('sms'));
        $this->assertFalse($preferences->isChannelEnabled('email'));
    }

    /**
     * Test unique constraint on notification preferences.
     */
    public function test_unique_constraint_on_user_notification_preferences(): void
    {
        $user = User::factory()->create();
        $this->expectException(\Illuminate\Database\QueryException::class);
        NotificationPreference::create([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test updating multiple preferences at once.
     */
    public function test_user_can_update_gift_preference(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'gift_received',
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'gift_received' => false,
        ]);
    }

    /**
     * Test updating pet activity notification preference.
     */
    public function test_user_can_update_pet_activity_preference(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'pet_activity',
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'pet_activity' => false,
        ]);
    }

    /**
     * Test updating caregiver invitation notification preference.
     */
    public function test_user_can_update_caregiver_invitation_preference(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'caregiver_invitation',
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'caregiver_invitation' => false,
        ]);
    }
}
