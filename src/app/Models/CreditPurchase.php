<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a credit purchase order.
 *
 * @group Credits
 */
class CreditPurchase extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'credit_bundle_id',
        'credits',
        'amount_cents',
        'stripe_session_id',
        'stripe_charge_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount_cents' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'credits', 'amount_cents']);
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function creditBundle(): BelongsTo
    {
        return $this->belongsTo(CreditBundle::class);
    }
}
