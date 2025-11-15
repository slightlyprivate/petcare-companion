<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a credit transaction.
 *
 * @group Credits
 */
class CreditTransaction extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'wallet_id',
        'amount',
        'amount_credits',
        'type',
        'reason',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'amount_credits' => 'integer',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['wallet_id', 'amount', 'amount_credits', 'type', 'reason', 'related_type', 'related_id']);
    }

    /**
     * Relationships
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
