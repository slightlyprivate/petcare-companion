<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a logged activity for a pet.
 *
 * @group Pets
 */
class PetActivity extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pet_id',
        'user_id',
        'type',
        'description',
        'media_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // No special casts yet; timestamps handled automatically.
        ];
    }

    /**
     * Configure Spatie activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['pet_id', 'user_id', 'type', 'description', 'media_url'])
            ->useLogName('pet_activity')
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the pet associated with this activity.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the user who created this activity (may be null if system logged).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
