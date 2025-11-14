<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a gift sent to a pet.
 *
 * @group Gifts
 */
class Gift extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'pet_id',
        'gift_type_id',
        'cost_in_credits',
        'stripe_session_id',
        'stripe_charge_id',
        'stripe_metadata',
        'status',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_in_credits' => 'integer',
        'completed_at' => 'datetime',
        'stripe_metadata' => 'array',
    ];

    /**
     * Configure the model's activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'pet_id', 'cost_in_credits', 'stripe_session_id', 'stripe_charge_id', 'status', 'completed_at']);
    }

    /**
     * Get the user that sent this gift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pet this gift was sent to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the gift type for this gift.
     */
    public function giftType(): BelongsTo
    {
        return $this->belongsTo(GiftType::class);
    }

    /**
     * Scope a query to only include successful gifts.
     */
    public function scopePaid($query): mixed
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending gifts.
     */
    public function scopePending($query): mixed
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark the gift as sent.
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the gift as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
