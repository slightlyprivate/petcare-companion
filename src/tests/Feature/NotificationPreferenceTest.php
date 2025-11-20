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
        $preferences = NotificationPreference::create([
            'user_id' => $user->id,
            'otp_notifications' => true,
            'login_notifications' => false,
            'gift_notifications' => true,
            'pet_update_notifications' => false,
            'sms_enabled' => true,
            'email_enabled' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user/notification-preferences');

        $response->assertOk()
            ->assertJsonPath('data.otp_notifications', true)
            ->assertJsonPath('data.login_notifications', false)
            ->assertJsonPath('data.gift_notifications', true)
            ->assertJsonPath('data.pet_update_notifications', false)
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
            ->assertJsonPath('data.otp_notifications', true)
            ->assertJsonPath('data.login_notifications', true)
            ->assertJsonPath('data.gift_notifications', true)
            ->assertJsonPath('data.pet_update_notifications', true)
            ->assertJsonPath('data.pet_create_notifications', true)
            ->assertJsonPath('data.pet_delete_notifications', true)
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
        NotificationPreference::create([
            'user_id' => $user->id,
            'otp_notifications' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'otp',
            'enabled' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'otp')
            ->assertJsonPath('data.enabled', false);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'otp_notifications' => false,
        ]);
    }

    /**
     * Test disabling all notifications.
     */
    public function test_user_can_disable_all_notifications(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $preferences = NotificationPreference::create([
            'user_id' => $user->id,
            'otp_notifications' => true,
            'login_notifications' => true,
            'gift_notifications' => true,
            'pet_update_notifications' => true,
            'pet_create_notifications' => true,
            'pet_delete_notifications' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/notification-preferences/disable-all');

        $response->assertOk()
            ->assertJsonPath('message', 'All notifications have been disabled.');

        $preferences->refresh();
        $this->assertFalse($preferences->otp_notifications);
        $this->assertFalse($preferences->login_notifications);
        $this->assertFalse($preferences->gift_notifications);
        $this->assertFalse($preferences->pet_update_notifications);
        $this->assertFalse($preferences->pet_create_notifications);
        $this->assertFalse($preferences->pet_delete_notifications);
    }

    /**
     * Test enabling all notifications.
     */
    public function test_user_can_enable_all_notifications(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $preferences = NotificationPreference::create([
            'user_id' => $user->id,
            'otp_notifications' => false,
            'login_notifications' => false,
            'gift_notifications' => false,
            'pet_update_notifications' => false,
            'pet_create_notifications' => false,
            'pet_delete_notifications' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/notification-preferences/enable-all');

        $response->assertOk()
            ->assertJsonPath('message', 'All notifications have been enabled.');

        $preferences->refresh();
        $this->assertTrue($preferences->otp_notifications);
        $this->assertTrue($preferences->login_notifications);
        $this->assertTrue($preferences->gift_notifications);
        $this->assertTrue($preferences->pet_update_notifications);
        $this->assertTrue($preferences->pet_create_notifications);
        $this->assertTrue($preferences->pet_delete_notifications);
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
        $preferences = NotificationPreference::create([
            'user_id' => $user->id,
            'otp_notifications' => true,
            'login_notifications' => false,
            'sms_enabled' => true,
            'email_enabled' => false,
        ]);

        $this->assertTrue($preferences->isNotificationEnabled('otp'));
        $this->assertFalse($preferences->isNotificationEnabled('login'));
        $this->assertTrue($preferences->isChannelEnabled('sms'));
        $this->assertFalse($preferences->isChannelEnabled('email'));
    }

    /**
     * Test unique constraint on notification preferences.
     */
    public function test_unique_constraint_on_user_notification_preferences(): void
    {
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
        ]);

        // Attempting to create another preference for the same user should fail
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
        NotificationPreference::create([
            'user_id' => $user->id,
            'gift_notifications' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'gift',
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'gift_notifications' => false,
        ]);
    }

    /**
     * Test updating pet_create notification preference.
     */
    public function test_user_can_update_pet_create_preference(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'pet_create_notifications' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'pet_create',
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'pet_create_notifications' => false,
        ]);
    }

    /**
     * Test updating pet_delete notification preference.
     */
    public function test_user_can_update_pet_delete_preference(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'pet_delete_notifications' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/user/notification-preferences', [
            'type' => 'pet_delete',
            'enabled' => false,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'pet_delete_notifications' => false,
        ]);
    }
}
