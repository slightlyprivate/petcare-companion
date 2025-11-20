<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model representing a configured routine for a pet.
 *
 * @group Pets
 *
 * @property int $id
 * @property string $pet_id
 * @property string $name
 * @property string|null $description
 * @property string $time_of_day
 * @property array $days_of_week
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Pet $pet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PetRoutineOccurrence> $occurrences
 */
class PetRoutine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pet_id',
        'name',
        'description',
        'time_of_day',
        'days_of_week',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            // Keeping time_of_day as string; custom formatting handled at API layer if needed.
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the pet this routine belongs to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get occurrences generated for this routine.
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(PetRoutineOccurrence::class);
    }
}
