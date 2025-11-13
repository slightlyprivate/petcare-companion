<?php

namespace App\Http\Resources\Pet\Directory;

/**
 * Resource representation of a public Pet with donation metadata.
 *
 * @group Pets
 */
class DirectoryPetResource extends \Illuminate\Http\Resources\Json\JsonResource
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
            'total_donations_cents' => $this->donations_sum_amount_cents ?? 0,
            'total_donations' => round(($this->donations_sum_amount_cents ?? 0) / 100, 2),
            'donation_count' => $this->donations->where('status', 'paid')->count(),
            'popularity_rank' => $this->donations_count ?? 0,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
