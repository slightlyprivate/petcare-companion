<?php

namespace App\Services\Pet;

use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service for managing pet activities.
 *
 * @group Services
 */
class PetActivityService
{
    /**
     * List activities for a pet with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listForPet(Pet $pet, array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        $query = PetActivity::query()->where('pet_id', $pet->getKey());

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Create a new activity for a pet.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Pet $pet, ?User $user, array $data): PetActivity
    {
        $activity = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user?->getKey(),
            'type' => $data['type'],
            'description' => $data['description'],
            'media_url' => $data['media_url'] ?? null,
        ]);

        // Log system-level activity (Spatie) for audit trail
        activity()
            ->performedOn($activity)
            ->causedBy($user)
            ->withProperties([
                'pet_id' => $pet->getKey(),
                'type' => $activity->type,
            ])
            ->log('pet_activity_created');

        return $activity;
    }

    /**
     * Delete an activity.
     */
    public function delete(PetActivity $activity, ?User $user): void
    {
        $petId = $activity->pet_id;
        $activity->delete();

        activity()
            ->performedOn($activity)
            ->causedBy($user)
            ->withProperties([
                'pet_id' => $petId,
                'activity_id' => $activity->getKey(),
            ])
            ->log('pet_activity_deleted');
    }
}
