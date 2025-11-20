<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Model representing a user in the pet care companion application.
 *
 * @group Authentication
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class,
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }

    /**
     * Configure the model's activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'role']);
    }

    /**
     * Get the pets that belong to the user.
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    /**
     * Get the gifts sent by the user.
     */
    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class);
    }

    /**
     * Get the user's wallet.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get all pet-user relationships.
     */
    public function petUsers(): HasMany
    {
        return $this->hasMany(PetUser::class);
    }

    /**
     * Get all pets associated with this user (owned and caregiving).
     */
    public function allPets(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'pet_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get only the pets this user owns.
     */
    public function ownedPets(): BelongsToMany
    {
        return $this->allPets()->wherePivot('role', 'owner');
    }

    /**
     * Get only the pets this user is a caregiver for.
     */
    public function caregivingPets(): BelongsToMany
    {
        return $this->allPets()->wherePivot('role', 'caregiver');
    }

    /**
     * Get caregiver invitations sent by this user.
     */
    public function sentCaregiverInvitations(): HasMany
    {
        return $this->hasMany(PetCaregiverInvitation::class, 'inviter_id');
    }

    /**
     * Get caregiver invitations received by this user.
     */
    public function receivedCaregiverInvitations(): HasMany
    {
        return $this->hasMany(PetCaregiverInvitation::class, 'invitee_id');
    }

    /**
     * Get pending caregiver invitations for this user's email.
     */
    public function pendingCaregiverInvitations()
    {
        return PetCaregiverInvitation::where('invitee_email', $this->email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    /**
     * Determine if the user has administrator privileges.
     */
    public function isAdmin(): bool
    {
        return $this->role?->isAdmin() ?? false;
    }
}
