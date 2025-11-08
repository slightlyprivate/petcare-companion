<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pet_id',
        'title',
        'scheduled_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * Get the pet that owns the appointment.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Scope a query to include only upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now());
    }

    /**
     * Scope a query to include only past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now());
    }

    /**
     * Scope a query to include appointments for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', now()->toDateString());
    }

    /**
     * Scope a query to include appointments for this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Check if the appointment is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->scheduled_at?->isFuture() ?? false;
    }

    /**
     * Check if the appointment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_at?->isPast() ?? false;
    }

    /**
     * Get the appointment's status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isUpcoming()) {
            return 'upcoming';
        }

        return 'completed';
    }

    /**
     * Get the time until the appointment (human readable).
     */
    public function getTimeUntilAttribute(): ?string
    {
        if (!$this->scheduled_at || $this->isOverdue()) {
            return null;
        }

        return $this->scheduled_at->diffForHumans();
    }
}
