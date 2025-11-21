<?php

namespace App\Http\Resources\Pet;

use App\Http\Resources\Appointment\AppointmentResource;

/**
 * Resource representation of a Pet.
 *
 * @group Pets
 */
class PetResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(\Illuminate\Http\Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'species' => $this->species,
            'breed' => $this->breed,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'owner_name' => $this->owner_name,
            'age' => $this->age,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'appointments_count' => $this->whenCounted('appointments'),
            'upcoming_appointments_count' => $this->when(
                $this->relationLoaded('upcomingAppointments'),
                fn () => $this->upcomingAppointments->count()
            ),
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
        ];
    }
}
