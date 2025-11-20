<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a single dated occurrence of a pet routine.
 *
 * @group Pets
 *
 * @property int $id
 * @property int $pet_routine_id
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon|null $completed_at
 * @property string|null $completed_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read PetRoutine $routine
 * @property-read User|null $completedBy
 */
class PetRoutineOccurrence extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Eager load routine by default since occurrences are almost always displayed with parent routine context.
     *
     * @var list<string>
     */
    protected $with = ['routine'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pet_routine_id',
        'date',
        'completed_at',
        'completed_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Configure Spatie activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['pet_routine_id', 'date', 'completed_at', 'completed_by'])
            ->useLogName('pet_routine_occurrence')
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the routine this occurrence belongs to.
     */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(PetRoutine::class, 'pet_routine_id');
    }

    /**
     * Get the user who completed this occurrence (if any).
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
