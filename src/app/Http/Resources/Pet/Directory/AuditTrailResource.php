<?php

namespace App\Http\Resources\Pet\Directory;

use App\Constants\CreditConstants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource representing a wallet transaction audit trail entry.
 *
 * Includes transaction details with wallet impact and reason for transparency.
 *
 * @group Pets
 */
class AuditTrailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amountCredits = $this['amount_credits'] ?? 0;
        $amountCents = CreditConstants::toCents($amountCredits);

        // Determine transaction type description
        $typeLabel = match ($this['type'] ?? 'unknown') {
            'purchase' => 'Credit Purchase',
            'deduction' => 'Gift Sent',
            'refund' => 'Refund',
            default => ucfirst($this['type'] ?? 'Unknown'),
        };

        return [
            'id' => $this['id'] ?? null,
            'type' => $this['type'] ?? null,
            'type_label' => $typeLabel,
            'reason' => $this['reason'] ?? null,
            'amount_credits' => $amountCredits,
            'amount_cents' => $amountCents,
            'amount_dollars' => $amountCents / 100,
            'related_type' => $this['related_type'] ?? null,
            'related_id' => $this['related_id'] ?? null,
            'timestamp' => $this['created_at'] ?? null,
        ];
    }
}
