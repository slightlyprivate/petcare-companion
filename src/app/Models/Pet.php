<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a pet in the pet care companion application.
 *
 * @group Pets
 */
class Pet extends Model
{
    /** @use HasFactory<\Database\Factories\PetFactory> */
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'species',
        'breed',
        'birth_date',
        'owner_name',
        'user_id',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Configure the model's activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'species', 'breed', 'birth_date', 'owner_name', 'is_public']);
    }

    /**
     * Get the pet's appointments.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the user that owns the pet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gifts sent to this pet.
     */
    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class);
    }

    /**
     * Get the pet's age in years.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->diffInYears(Carbon::now());
    }

    /**
     * Get the pet's upcoming appointments.
     */
    public function upcomingAppointments(): HasMany
    {
        return $this->appointments()
            ->where('scheduled_at', '>=', Carbon::now())
            ->orderBy('scheduled_at', 'asc');
    }

    /**
     * Get the pet's past appointments.
     */
    public function pastAppointments(): HasMany
    {
        return $this->appointments()
            ->where('scheduled_at', '<', Carbon::now())
            ->orderBy('scheduled_at', 'desc');
    }

    /**
     * Scope a query to filter pets by species.
     */
    public function scopeBySpecies($query, string $species)
    {
        return $query->where('species', $species);
    }

    /**
     * Scope a query to filter pets by owner name.
     */
    public function scopeByOwner($query, string $ownerName)
    {
        return $query->where('owner_name', 'like', '%'.$ownerName.'%');
    }

    /**
     * Scope a query to search pets by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    /**
     * Get the pet's full display name with species.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->species})";
    }

    /**
     * Get the pet's age category.
     */
    public function getAgeCategoryAttribute(): ?string
    {
        $age = $this->age;

        if ($age === null) {
            return null;
        }

        return match (true) {
            $age < 1 => 'Baby',
            $age < 3 => 'Young',
            $age < 7 => 'Adult',
            default => 'Senior'
        };
    }

    /**
     * Check if the pet has any upcoming appointments.
     */
    public function hasUpcomingAppointments(): bool
    {
        return $this->upcomingAppointments()->exists();
    }

    /**
     * Get the next upcoming appointment.
     */
    public function nextAppointment(): ?Appointment
    {
        return $this->upcomingAppointments()->first();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a pet is deleted, all appointments should be deleted (cascade)
        static::deleting(function ($pet) {
            $pet->appointments()->delete();
        });
    }
}
