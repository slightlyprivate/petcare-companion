<?php

namespace App\Http\Resources\Pet\Directory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Enhanced pet profile resource with gift summaries and audit trail.
 *
 * Provides comprehensive reporting on gifts received and wallet transactions
 * for transparency in the public pet directory.
 *
 * @group Pets
 */
class PublicPetReportResource extends JsonResource
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

            // Gift statistics
            'gift_count' => $this->when(
                $this->relationLoaded('gifts'),
                fn () => $this->gifts->where('status', 'paid')->count()
            ),
            'total_gifts_value' => $this->whenAppended('total_gifts_value'),
            'total_gifts_cents' => $this->whenAppended('total_gifts_cents'),

            // Per-type gift summaries
            'gift_summaries_by_type' => $this->when(
                $this->relationLoaded('gifts'),
                fn () => $this->getGiftSummariesByType()
            ),

            // Wallet audit trail (if gift data includes wallet transaction info)
            'transaction_audit_trail' => $this->when(
                $this->relationLoaded('gifts'),
                fn () => $this->getTransactionAuditTrail()
            ),
        ];
    }

    /**
     * Get gift summaries grouped by gift type.
     *
     * @return array<array<string, mixed>>
     */
    private function getGiftSummariesByType(): array
    {
        $gifts = $this->gifts->where('status', 'paid');

        $summaries = $gifts
            ->groupBy(fn ($gift) => $gift->gift_type_id)
            ->map(function ($typeGifts, $giftTypeId) {
                $giftType = $typeGifts->first()?->giftType;

                return [
                    'gift_type_id' => $giftTypeId,
                    'gift_type_name' => $giftType?->name ?? 'Unknown',
                    'gift_type_icon' => $giftType?->icon_emoji ?? null,
                    'gift_type_color' => $giftType?->color_code ?? null,
                    'count' => $typeGifts->count(),
                    'total_credits' => $typeGifts->sum('cost_in_credits'),
                ];
            })
            ->values()
            ->all();

        return array_map(
            fn ($summary) => new GiftSummaryResource($summary),
            $summaries
        );
    }

    /**
     * Get transaction audit trail from gift wallet deductions.
     *
     * Provides transparency by showing all credit transactions related to gifts sent.
     *
     * @return array<array<string, mixed>>
     */
    private function getTransactionAuditTrail(): array
    {
        $gifts = $this->gifts->where('status', 'paid');

        // Build audit trail entries from gifts and their associated transactions
        $auditTrail = $gifts
            ->map(function ($gift) {
                return [
                    'id' => "gift_{$gift->id}",
                    'type' => 'deduction',
                    'reason' => "Gift sent to {$this->name}",
                    'amount_credits' => $gift->cost_in_credits,
                    'related_type' => 'Gift',
                    'related_id' => $gift->id,
                    'created_at' => $gift->completed_at?->toIso8601String(),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return array_map(
            fn ($entry) => new AuditTrailResource($entry),
            $auditTrail
        );
    }
}
