<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource class for representing a credit purchase.
 */
class CreditPurchaseResource extends JsonResource
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
            'user_id' => $this->user_id,
            'wallet_id' => $this->wallet_id,
            'credit_bundle_id' => $this->credit_bundle_id,
            'credits' => $this->credits,
            'amount_cents' => $this->amount_cents,
            'amount_dollars' => $this->amount_cents / 100,
            'status' => $this->status,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
