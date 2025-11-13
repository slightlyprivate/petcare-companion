<?php

namespace App\Http\Resources\Appointment;

use App\Http\Resources\Pet\PetResource;

/**
 * Resource representation of an Appointment.
 *
 * @group Appointments
 */
class AppointmentResource extends \Illuminate\Http\Resources\Json\JsonResource
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
            'pet_id' => $this->pet_id,
            'title' => $this->title,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'scheduled_at_formatted' => $this->scheduled_at?->format('Y-m-d H:i'),
            'notes' => $this->notes,
            'is_upcoming' => $this->scheduled_at?->isFuture() ?? false,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'pet' => new PetResource($this->whenLoaded('pet')),
        ];
    }
}
