<?php

namespace App\Services\Pet;

use App\Constants\CreditConstants;
use App\Exceptions\Gift\CreditCostRequiredException;
use App\Exceptions\Stripe\PaymentSessionFailed;
use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use Stripe\Stripe;

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
        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));

        $costInCredits = (int) $data['cost_in_credits'];

        if ($costInCredits <= 0) {
            throw new CreditCostRequiredException('Gift cost must be greater than zero credits.');
        }

        // Create gift record
        $gift = Gift::create([
            'id' => $this->generateUniqueId(),
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => $costInCredits,
            'status' => 'pending',
        ]);

        try {
            $session = $this->createStripeCheckoutSession($gift, $data['return_url']);
        } catch (\Exception $e) {
            // Mark gift as failed if Stripe session creation fails
            $gift->markAsFailed();
            throw new PaymentSessionFailed;
        }

        // Update the gift with the Stripe session ID
        $gift->stripe_session_id = $session->id;
        $gift->save();

        return [
            'gift_id' => $gift->id,
            'checkout_url' => $session->url,
            'cost_in_credits' => $costInCredits,
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'owner_name' => $pet->owner_name,
            ],
        ];
    }

    /**
     * Create a Stripe Checkout Session for the gift.
     */
    protected function createStripeCheckoutSession(Gift $gift, string $returnUrl): \Stripe\Checkout\Session
    {
        $pet = $gift->pet;
        // Convert credits to cents using the standardized credit constant
        $amountCents = CreditConstants::toCents($gift->cost_in_credits);

        return \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Send Gift to {$pet->name}",
                            'description' => "Send a gift to {$pet->name} ({$pet->species}) owned by {$pet->owner_name}",
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $returnUrl.'?gift_id={CHECKOUT_SESSION_ID}&status=success',
            'cancel_url' => $returnUrl.'?gift_id={CHECKOUT_SESSION_ID}&status=cancel',
            'metadata' => [
                'gift_id' => $gift->id,
            ],
        ]);
    }

    /**
     * Generate a unique identifier for a gift.
     */
    protected function generateUniqueId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}
