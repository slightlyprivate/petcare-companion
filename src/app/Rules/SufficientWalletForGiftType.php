<?php

namespace App\Rules;

use App\Models\GiftType;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensures the user's wallet balance covers the selected gift type cost.
 */
class SufficientWalletForGiftType implements ValidationRule
{
    public function __construct(private ?User $user = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->user) {
            return; // Skip during doc generation or unauthenticated contexts
        }

        $giftType = GiftType::where('id', $value)->where('is_active', true)->first();
        if (! $giftType) {
            // Let the core exists rule handle invalid IDs/inactive types
            return;
        }

        $wallet = $this->user->wallet;
        $required = (int) $giftType->cost_in_credits;
        $available = (int) ($wallet?->balance_credits ?? 0);

        if (! $wallet || $available < $required) {
            $fail(__('wallet.errors.insufficient_balance', [
                'required' => $required,
                'available' => $available,
            ]));
        }
    }
}

