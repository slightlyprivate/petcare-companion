<?php

namespace App\Http\Resources\Pet\Directory;

use App\Constants\CreditConstants;

/**
 * Resource representation of a public Pet with gift metadata.
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
        // Calculate total gift credits (gifts_sum_cost_in_credits is the sum added by withSum in service)
        $totalCredits = $this->gifts_sum_cost_in_credits ?? 0;
        // Convert credits to cents using the standardized credit constant (1 credit = $1.00 = 100 cents)
        $totalCents = CreditConstants::toCents($totalCredits);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'species' => $this->species,
            'breed' => $this->breed,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'owner_name' => $this->owner_name,
            'age' => $this->age,
            'gift_count' => $this->gifts->where('status', 'paid')->count(),
            'total_gifts_cents' => $totalCents,
            'total_gifts' => $totalCents / 100,
            'popularity_rank' => $this->gifts_count ?? 0,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
