<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'species' => $this->species,
            'breed' => $this->breed,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'owner_name' => $this->owner_name,
            'age' => $this->age,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'appointments_count' => $this->whenCounted('appointments'),
            'upcoming_appointments_count' => $this->when(
                $this->relationLoaded('upcomingAppointments'),
                fn() => $this->upcomingAppointments->count()
            ),
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
        ];
    }
}
