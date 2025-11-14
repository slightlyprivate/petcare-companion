<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
