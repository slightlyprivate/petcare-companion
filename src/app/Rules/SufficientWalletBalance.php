<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule to ensure user has sufficient wallet balance.
 *
 * Checks that the user's wallet has at least the requested number of credits.
 */
class SufficientWalletBalance implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(protected ?User $user = null) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // During documentation generation, there may be no authenticated user.
        // Skip validation in that context to avoid breaking Scribe.
        if (! $this->user) {
            return;
        }

        $wallet = $this->user->wallet;

        if (! $wallet || $wallet->balance_credits < $value) {
            $message = __('wallet.errors.insufficient_balance', [
                'required' => $value,
                'available' => $wallet?->balance_credits ?? 0,
            ]);
            $fail($message);
        }
    }
}
