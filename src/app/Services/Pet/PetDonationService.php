<?php

namespace App\Services\Pet;

use App\Exceptions\Donation\AmountRequiredException;
use App\Exceptions\Stripe\PaymentSessionFailed;
use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Stripe\Stripe;

/**
 * Service for handling pet donations.
 */
class PetDonationService
{
    /**
     * Create a donation for a specific pet.
     *
     * @throws AmountRequiredException if the donation amount is invalid.
     * @throws PaymentSessionFailed if the Stripe payment session creation fails.
     */
    public function createDonation(array $data, User $user, Pet $pet): array
    {
        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));
        // Convert amount to cents
        $amountCents = (int) ($data['amount'] * 100);

        if ($amountCents <= 0) {
            throw new AmountRequiredException('Donation amount must be greater than zero.');
        }

        // Create donation record
        $donation = Donation::create([
            'id' => $this->generateUniqueId(),
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'amount_cents' => $amountCents,
            'status' => 'pending',
        ]);

        try {
            $session = $this->createStripeCheckoutSession($donation);
        } catch (\Exception $e) {
            // Mark donation as failed if Stripe session creation fails
            $donation->markAsFailed();
            throw new PaymentSessionFailed;
        }

        // Update the donation with the Stripe session ID
        $donation->stripe_session_id = $session->id;
        $donation->save();

        return [
            'donation_id' => $donation->id,
            'checkout_url' => $session->url,
            'amount' => $data['amount'],
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'owner_name' => $pet->owner_name,
            ],
        ];
    }

    /**
     * Create a Stripe Checkout Session for the donation.
     */
    protected function createStripeCheckoutSession(Donation $donation): \Stripe\Checkout\Session
    {
        $pet = $donation->pet;

        return \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Donate to {$pet->name}",
                            'description' => "Support {$pet->name} ({$pet->species}) owned by {$pet->owner_name}",
                        ],
                        'unit_amount' => $donation->amount_cents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => config('app.url').'/donations/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url').'/donations/cancel',
            'metadata' => [
                'donation_id' => $donation->id,
            ],
        ]);
    }

    /**
     * Generate a unique identifier for a donation.
     */
    protected function generateUniqueId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}
