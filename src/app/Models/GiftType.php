<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model representing a gift type category.
 *
 * Gift types help users choose what kind of gift to send and organize
 * the gifting experience with categories, icons, and descriptions.
 *
 * @group Gift Types
 */
class GiftType extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'icon_emoji',
        'color_code',
        'cost_in_credits',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'cost_in_credits' => 'integer',
    ];

    /**
     * Get the gifts that belong to this gift type.
     */
    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class);
    }

    /**
     * Scope a query to only include active gift types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include visible gift types (ordered).
     */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
