<?php

namespace App\Services\Credit;

use App\Constants\CreditConstants;
use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Service for managing credit purchase operations.
 */
class CreditPurchaseService
{
    /**
     * Create a Stripe checkout session for a credit bundle purchase.
     *
     * @param  User  $user  The user purchasing credits
     * @param  CreditBundle  $bundle  The credit bundle to purchase
     * @param  string  $returnUrl  The URL to return to after checkout
     * @return array Array containing the session, purchase record, and checkout URL
     *
     * @throws \Exception
     */
    public function createCheckoutSession(User $user, CreditBundle $bundle, string $returnUrl): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Ensure user has a wallet
        $wallet = $user->wallet ?: $user->wallet()->create(['balance_credits' => 0]);

        // Create a credit purchase record in pending state
        $purchase = CreditPurchase::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => $bundle->credits,
            'amount_cents' => $bundle->price_cents,
            'status' => 'pending',
        ]);

        // Create Stripe checkout session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $bundle->name,
                            'description' => "{$bundle->credits} credits",
                        ],
                        'unit_amount' => $bundle->price_cents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $returnUrl.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $returnUrl,
            'client_reference_id' => $purchase->id,
            'metadata' => [
                'purchase_id' => $purchase->id,
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'credits' => $bundle->credits,
            ],
        ]);

        // Update purchase with session ID
        $purchase->update(['stripe_session_id' => $session->id]);

        return [
            'purchase' => $purchase,
            'session' => $session,
            'checkout_url' => $session->url,
        ];
    }

    /**
     * Complete a credit purchase after successful payment.
     *
     * @param  string  $stripeSessionId  The Stripe session ID
     * @param  string  $chargeId  The Stripe charge ID (optional)
     *
     * @throws \Exception
     */
    public function completePurchase(string $stripeSessionId, ?string $chargeId = null): CreditPurchase
    {
        $purchase = CreditPurchase::where('stripe_session_id', $stripeSessionId)->firstOrFail();

        if ($purchase->status === 'completed') {
            return $purchase;
        }

        $wallet = $purchase->wallet;

        // Update wallet balance
        $wallet->increment('balance_credits', $purchase->credits);

        // Update purchase record
        $purchase->update([
            'stripe_charge_id' => $chargeId,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Log credit transaction (store amount in cents and credits for consistency)
        $wallet->transactions()->create([
            'amount' => CreditConstants::toCents($purchase->credits),
            'amount_credits' => $purchase->credits,
            'type' => 'purchase',
            'related_type' => 'credit_purchase',
            'related_id' => $purchase->id,
        ]);

        return $purchase;
    }

    /**
     * Mark a credit purchase as failed.
     *
     * @param  string  $stripeSessionId  The Stripe session ID
     *
     * @throws \Exception
     */
    public function failPurchase(string $stripeSessionId): CreditPurchase
    {
        $purchase = CreditPurchase::where('stripe_session_id', $stripeSessionId)->firstOrFail();

        $purchase->update(['status' => 'failed']);

        return $purchase;
    }
}
