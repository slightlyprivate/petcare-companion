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
        'donation_notifications',
        'pet_update_notifications',
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
        'donation_notifications' => 'boolean',
        'pet_update_notifications' => 'boolean',
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
                'donation_notifications',
                'pet_update_notifications',
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
        $attribute = "{$type}_notifications";

        return $this->getAttribute($attribute) ?? true;
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
            'donation_notifications' => false,
            'pet_update_notifications' => false,
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
            'donation_notifications' => true,
            'pet_update_notifications' => true,
        ]);
    }
}
