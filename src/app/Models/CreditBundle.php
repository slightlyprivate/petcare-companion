<?php

namespace App\Models;

use App\Constants\CreditConstants;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Model representing a credit bundle offered for purchase.
 *
 * @group Credits
 */
class CreditBundle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'credits',
        'price_cents',
        'is_active',
    ];

    protected $casts = [
        'credits' => 'integer',
        'price_cents' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Ensure price_cents matches the expected value based on credits before saving.
     */
    protected static function booted(): void
    {
        static::saving(function (self $bundle) {
            $expected = CreditConstants::toCents((int) $bundle->credits);
            if ($bundle->price_cents !== $expected) {
                Log::warning('CreditBundle price_cents adjusted to match credit ratio', [
                    'bundle_id' => $bundle->id,
                    'credits' => $bundle->credits,
                    'provided_price_cents' => $bundle->price_cents,
                    'expected_price_cents' => $expected,
                ]);
                $bundle->price_cents = $expected;
            }
        });
    }
}
