<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory, HasUuids;

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

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
