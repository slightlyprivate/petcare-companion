<?php

namespace App\Services\Pet;

use App\Constants\CreditConstants;
use App\Exceptions\Gift\CreditCostRequiredException;
use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;

/**
 * Service for handling pet gifts.
 */
class PetGiftService
{
    /**
     * Create a gift for a specific pet.
     *
     * @throws CreditCostRequiredException if the gift cost is invalid.
     * @throws PaymentSessionFailed if the Stripe payment session creation fails.
     */
    public function createGift(array $data, User $user, Pet $pet): array
    {
        // Enforce catalog price based on selected gift type
        $giftTypeId = $data['gift_type_id'] ?? null;
        $giftType = \App\Models\GiftType::where('id', $giftTypeId)->where('is_active', true)->firstOrFail();
        $costInCredits = (int) $giftType->cost_in_credits;

        // Atomic creation + deduction to avoid race conditions
        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $pet, $giftTypeId, $costInCredits) {
            $wallet = $user->wallet()->lockForUpdate()->first();

            if (! $wallet) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'gift_type_id' => __('wallet.errors.insufficient_balance', [
                        'required' => $costInCredits,
                        'available' => 0,
                    ]),
                ]);
            }

            if ($wallet->balance_credits < $costInCredits) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'gift_type_id' => __('wallet.errors.insufficient_balance', [
                        'required' => $costInCredits,
                        'available' => $wallet->balance_credits,
                    ]),
                ]);
            }

            // Create gift record in pending state with catalog association
            $gift = Gift::create([
                'id' => $this->generateUniqueId(),
                'user_id' => $user->id,
                'pet_id' => $pet->id,
                'gift_type_id' => $giftTypeId,
                'cost_in_credits' => $costInCredits,
                'status' => 'pending',
            ]);

            // Deduct credits immediately from wallet and record transaction
            $wallet->decrement('balance_credits', $costInCredits);
            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => CreditConstants::toCents($costInCredits),
                'amount_credits' => $costInCredits,
                'reason' => 'gift_sent',
                'related_type' => 'gift',
                'related_id' => $gift->id,
            ]);

            // Mark gift as paid/completed since wallet credits cover the cost
            $gift->markAsPaid();

            // Send notification after gift is successfully paid
            if (\App\Helpers\NotificationHelper::isNotificationEnabled($user, 'gift_send')) {
                \Illuminate\Support\Facades\Notification::send($user, new \App\Notifications\Gift\GiftSuccessNotification($gift));
            }

            return [
                'gift_id' => $gift->id,
                'status' => $gift->status,
                'cost_in_credits' => $costInCredits,
                'pet' => [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'species' => $pet->species,
                    'owner_name' => $pet->owner_name,
                ],
            ];
        });
    }

    /**
     * Create a Stripe Checkout Session for the gift.
     */
    // Stripe checkout session creation removed: gifts are funded via wallet credits

    /**
     * Generate a unique identifier for a gift.
     */
    protected function generateUniqueId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    /**
     * Deduct credits from user's wallet and create a transaction record.
     *
     * Uses atomic operations to prevent race conditions.
     */
    protected function deductCreditsFromWallet(User $user, int $credits, ?string $giftId = null): void
    {
        // Get wallet with lock to prevent concurrent modification
        $wallet = $user->wallet()->lockForUpdate()->first();

        if ($wallet) {
            // Decrement wallet balance
            $wallet->decrement('balance_credits', $credits);

            // Log the transaction in cents for consistency
            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => CreditConstants::toCents($credits),
                'amount_credits' => $credits,
                'reason' => 'gift_sent',
                'related_type' => 'gift',
                'related_id' => $giftId,
            ]);
        }
    }
}
