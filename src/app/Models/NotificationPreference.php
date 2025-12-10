<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model for storing user notification preferences.
 */
class NotificationPreference extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'otp_notifications',
        'login_notifications',
        'gift_notifications',
        'pet_update_notifications',
        'pet_create_notifications',
        'pet_delete_notifications',
        'pet_activity',
        'appointment_created',
        'caregiver_invitation',
        'gift_received',
        'routine_reminder',
        'sms_enabled',
        'email_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'otp_notifications' => 'boolean',
        'login_notifications' => 'boolean',
        'gift_notifications' => 'boolean',
        'pet_update_notifications' => 'boolean',
        'pet_create_notifications' => 'boolean',
        'pet_delete_notifications' => 'boolean',
        'pet_activity' => 'boolean',
        'appointment_created' => 'boolean',
        'caregiver_invitation' => 'boolean',
        'gift_received' => 'boolean',
        'routine_reminder' => 'boolean',
        'sms_enabled' => 'boolean',
        'email_enabled' => 'boolean',
    ];

    /**
     * Configure the model's activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'otp_notifications',
                'login_notifications',
                'gift_notifications',
                'pet_update_notifications',
                'pet_create_notifications',
                'pet_delete_notifications',
                'pet_activity',
                'appointment_created',
                'caregiver_invitation',
                'gift_received',
                'routine_reminder',
                'sms_enabled',
                'email_enabled',
            ]);
    }

    /**
     * Get the user that owns this preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a specific notification type is enabled.
     */
    public function isNotificationEnabled(string $type): bool
    {
        $column = $this->mapNotificationColumn($type);

        if (! $column) {
            $defaults = config('notifications.defaults.notifications', []);

            return (bool) ($defaults[$type] ?? true);
        }

        return (bool) $this->getAttribute($column);
    }

    /**
     * Check if a specific channel is enabled.
     */
    public function isChannelEnabled(string $channel): bool
    {
        $attribute = "{$channel}_enabled";

        return $this->getAttribute($attribute) ?? true;
    }

    /**
     * Disable all notifications.
     */
    public function disableAll(): void
    {
        $this->update([
            'otp_notifications' => false,
            'login_notifications' => false,
            'gift_notifications' => false,
            'pet_update_notifications' => false,
            'pet_create_notifications' => false,
            'pet_delete_notifications' => false,
            'pet_activity' => false,
            'appointment_created' => false,
            'caregiver_invitation' => false,
            'gift_received' => false,
            'routine_reminder' => false,
        ]);
    }

    /**
     * Enable all notifications.
     */
    public function enableAll(): void
    {
        $this->update([
            'otp_notifications' => true,
            'login_notifications' => true,
            'gift_notifications' => true,
            'pet_update_notifications' => true,
            'pet_create_notifications' => true,
            'pet_delete_notifications' => true,
            'pet_activity' => true,
            'appointment_created' => true,
            'caregiver_invitation' => true,
            'gift_received' => true,
            'routine_reminder' => true,
        ]);
    }

    private function mapNotificationColumn(string $type): ?string
    {
        return match ($type) {
            'otp', 'otp_notifications' => 'otp_notifications',
            'login', 'login_notifications' => 'login_notifications',
            'gift', 'gift_notifications', 'gift_send', 'gift_send_notifications' => 'gift_notifications',
            'pet_update', 'pet_update_notifications' => 'pet_update_notifications',
            'pet_create', 'pet_create_notifications' => 'pet_create_notifications',
            'pet_delete', 'pet_delete_notifications' => 'pet_delete_notifications',
            'pet_activity' => 'pet_activity',
            'appointment_created', 'appointment' => 'appointment_created',
            'caregiver_invitation' => 'caregiver_invitation',
            'gift_received' => 'gift_received',
            'routine_reminder', 'routine' => 'routine_reminder',
            default => null,
        };
    }
}
