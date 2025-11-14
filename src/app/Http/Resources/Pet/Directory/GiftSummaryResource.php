<?php

namespace App\Http\Resources\Pet\Directory;

use App\Constants\CreditConstants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource representing a per-type gift summary for a pet.
 *
 * Aggregates gift statistics by gift type, showing count and total value.
 *
 * @group Pets
 */
class GiftSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $giftCount = $this['count'] ?? 0;
        $totalCredits = $this['total_credits'] ?? 0;
        $totalCents = CreditConstants::toCents($totalCredits);

        return [
            'gift_type_id' => $this['gift_type_id'] ?? null,
            'gift_type_name' => $this['gift_type_name'] ?? 'Unknown',
            'gift_type_icon' => $this['gift_type_icon'] ?? null,
            'gift_type_color' => $this['gift_type_color'] ?? null,
            'count' => $giftCount,
            'total_value_cents' => $totalCents,
            'total_value' => $totalCents / 100,
            'average_value' => $giftCount > 0 ? ($totalCents / $giftCount) / 100 : 0,
        ];
    }
}
