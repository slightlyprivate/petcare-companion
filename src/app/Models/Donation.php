<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a donation made to a pet.
 *
 * @group Donations
 */
class Donation extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'pet_id',
        'amount_cents',
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
        'amount_cents' => 'integer',
        'completed_at' => 'datetime',
        'stripe_metadata' => 'array',
    ];

    /**
     * Configure the model's activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'pet_id', 'amount_cents', 'stripe_session_id', 'stripe_charge_id', 'status', 'completed_at']);
    }

    /**
     * Get the user that made this donation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pet this donation was made to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the donation amount in dollars.
     */
    public function getAmountDollarsAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    /**
     * Scope a query to only include successful donations.
     */
    public function scopePaid($query): mixed
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending donations.
     */
    public function scopePending($query): mixed
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark the donation as paid.
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the donation as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
