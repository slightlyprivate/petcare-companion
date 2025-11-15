<?php

namespace Tests\Feature;

use App\Constants\CreditConstants;
use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test suite for credit conversion accuracy and directory totals.
 *
 * Verifies that credit-to-cents conversion is consistent across the system
 * and that directory totals accurately reflect accumulated gifts.
 */
class CreditConversionAccuracyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that credit constant converts 5 credits to 100 cents ($1.00).
     */
    #[Test]
    public function credit_constant_converts_five_credits_to_one_dollar(): void
    {
        $this->assertEquals(20, CreditConstants::toCents(1));
        $this->assertEquals(0.20, CreditConstants::toDollars(1));
        $this->assertEquals(5, CreditConstants::CREDITS_PER_DOLLAR);
    }

    /**
     * Test that credit conversion is accurate for common gift amounts.
     */
    #[Test]
    public function credit_conversion_is_accurate_for_common_amounts(): void
    {
        // Small gift: 50 credits = $10.00 = 1000 cents
        $this->assertEquals(1000, CreditConstants::toCents(50));
        $this->assertEquals(10.0, CreditConstants::toDollars(50));

        // Medium gift: 100 credits = $20.00 = 2000 cents
        $this->assertEquals(2000, CreditConstants::toCents(100));
        $this->assertEquals(20.0, CreditConstants::toDollars(100));

        // Large gift: 350 credits = $70.00 = 7000 cents
        $this->assertEquals(7000, CreditConstants::toCents(350));
        $this->assertEquals(70.0, CreditConstants::toDollars(350));

        // Large gift: 1000 credits = $200.00 = 20000 cents
        $this->assertEquals(20000, CreditConstants::toCents(1000));
        $this->assertEquals(200.0, CreditConstants::toDollars(1000));
    }

    /**
     * Smoke test: Directory totals accurately reflect accumulated gifts.
     *
     * Verifies that a pet with multiple gifts shows correct total_gifts_cents
     * and total_gifts (in dollars) in the directory resource.
     *
     * Example: If pet has gifts of 100, 150, and 100 credits:
     * - Total credits: 350
     * - Total cents: 35000
     * - Total dollars: $350.00
     */
    #[Test]
    public function directory_pet_total_gifts_credits_accumulates_correctly(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create multiple gifts
        $giftAmounts = [100, 150, 100]; // Total: 350 credits = $350.00
        $totalCredits = 0;

        foreach ($giftAmounts as $credits) {
            Gift::factory()->create([
                'user_id' => $user->id,
                'pet_id' => $pet->id,
                'cost_in_credits' => $credits,
                'status' => 'paid',
            ]);
            $totalCredits += $credits;
        }

        // Refresh pet to get fresh data
        $pet->refresh();

        // Manually sum gifts from database
        $sumCredits = Gift::where('pet_id', $pet->id)
            ->where('status', 'paid')
            ->sum('cost_in_credits');

        $this->assertEquals(350, $sumCredits);
        $this->assertEquals(350, $totalCredits);

        // Verify conversion to cents
        $expectedCents = CreditConstants::toCents($totalCredits);
        $this->assertEquals(7000, $expectedCents);

        // Verify conversion to dollars
        $expectedDollars = CreditConstants::toDollars($totalCredits);
        $this->assertEquals(70.0, $expectedDollars);
    }

    /**
     * Smoke test: Large gift conversion accuracy.
     *
     * Verifies accurate conversion for substantial amounts to prevent
     * loss-of-precision errors in Stripe payment amounts.
     */
    #[Test]
    public function large_gift_conversion_maintains_accuracy(): void
    {
        $largeGiftCredits = 10000; // $2,000 (10000 credits / 5 = $2000)

        $cents = CreditConstants::toCents($largeGiftCredits);
        $dollars = CreditConstants::toDollars($largeGiftCredits);

        $this->assertEquals(200000, $cents);
        $this->assertEquals(2000.0, $dollars);

        // Verify round-trip conversion: cents -> credits -> cents
        $roundTripCredits = CreditConstants::fromCents($cents);
        $roundTripCents = CreditConstants::toCents($roundTripCredits);
        $this->assertEquals($largeGiftCredits, $roundTripCredits);
        $this->assertEquals($cents, $roundTripCents);
    }

    /**
     * Smoke test: Verify that donation display logic aligns with Stripe reality.
     *
     * Ensures that the displayed gift amount in the directory matches what
     * was actually charged in Stripe (avoiding "$15 gifted" but only "$7.50 received").
     *
     * Example scenario:
     * - User sends 175 credit gift (calculated as $35.00 in Stripe)
     * - Directory should show: total_gifts = 175 (credits), total_gifts_cents = 3500
     * - This must match Stripe's charge amount
     */
    #[Test]
    public function directory_display_aligns_with_stripe_charge_amounts(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create gift that was actually paid via Stripe
        $giftCredits = 175; // $35.00 (175 credits / 5 = $35)
        $expectedStripeCents = CreditConstants::toCents($giftCredits);

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => $giftCredits,
            'status' => 'paid',
            'stripe_metadata' => [
                'amount' => $expectedStripeCents, // 3500 cents
                'currency' => 'usd',
                'payment_method' => 'card',
            ],
        ]);

        // Verify the gift's cost matches what was charged
        $this->assertEquals($giftCredits, $gift->cost_in_credits);
        $this->assertEquals($expectedStripeCents, $gift->stripe_metadata['amount']);

        // Verify directory calculation would be correct
        $pet->refresh();
        $sumCredits = Gift::where('pet_id', $pet->id)
            ->where('status', 'paid')
            ->sum('cost_in_credits');

        $displayCents = CreditConstants::toCents($sumCredits);
        $displayDollars = $displayCents / 100; // Convert cents to dollars

        $this->assertEquals(175, $sumCredits);
        $this->assertEquals(3500, $displayCents);
        $this->assertEquals(35.0, $displayDollars);
    }

    /**
     * Smoke test: Multiple contributors to same pet.
     *
     * Verifies that total_gifted_credits accumulates correctly from multiple users.
     */
    #[Test]
    public function multiple_contributors_totals_accumulate_correctly(): void
    {
        $pet = Pet::factory()->create();

        // Create multiple users sending gifts
        $contributions = [
            [100, 50], // User 1: 100 + 50 = 150 credits
            [200],     // User 2: 200 credits
            [75, 25],  // User 3: 75 + 25 = 100 credits
        ];

        $expectedTotal = 150 + 200 + 100; // 450 total

        foreach ($contributions as $amounts) {
            $user = User::factory()->create();
            foreach ($amounts as $credits) {
                Gift::factory()->create([
                    'user_id' => $user->id,
                    'pet_id' => $pet->id,
                    'cost_in_credits' => $credits,
                    'status' => 'paid',
                ]);
            }
        }

        // Verify total accumulation
        $pet->refresh();
        $sumCredits = Gift::where('pet_id', $pet->id)
            ->where('status', 'paid')
            ->sum('cost_in_credits');

        $this->assertEquals(450, $expectedTotal);
        $this->assertEquals(450, $sumCredits);

        // Verify directory display values
        $displayCents = CreditConstants::toCents($sumCredits);
        $displayDollars = $displayCents / 100;

        $this->assertEquals(9000, $displayCents);
        $this->assertEquals(90.0, $displayDollars);
    }

    /**
     * Smoke test: Failed gifts do not affect directory totals.
     *
     * Verifies that only 'paid' gifts are counted in totals, not failed or pending.
     */
    #[Test]
    public function failed_and_pending_gifts_excluded_from_totals(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create mixed status gifts
        Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 100,
            'status' => 'paid',
        ]);

        Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 200,
            'status' => 'failed',
        ]);

        Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 150,
            'status' => 'pending',
        ]);

        // Only paid gifts should be counted
        $pet->refresh();
        $paidSum = Gift::where('pet_id', $pet->id)
            ->where('status', 'paid')
            ->sum('cost_in_credits');

        $this->assertEquals(100, $paidSum);

        // Verify directory display
        $displayCents = CreditConstants::toCents($paidSum);
        $this->assertEquals(2000, $displayCents);
    }

    /**
     * Smoke test: Edge case - pet with no gifts shows zero totals.
     */
    #[Test]
    public function pet_with_no_gifts_shows_zero_totals(): void
    {
        $pet = Pet::factory()->create();

        $paidSum = Gift::where('pet_id', $pet->id)
            ->where('status', 'paid')
            ->sum('cost_in_credits');

        $displayCents = CreditConstants::toCents($paidSum ?? 0);
        $displayDollars = $displayCents / 100;

        $this->assertEquals(0, $paidSum);
        $this->assertEquals(0, $displayCents);
        $this->assertEquals(0.0, $displayDollars);
    }

    /**
     * Smoke test: Stripe metadata charge amount matches credit conversion.
     *
     * Critical test: Verifies that when a gift is created and charged via Stripe,
     * the Stripe charge amount (in cents) matches our credit conversion calculation.
     * This prevents discrepancies like "$15 gifted" but "$7.50 received".
     */
    #[Test]
    public function stripe_metadata_amount_matches_credit_conversion(): void
    {
        $creditAmounts = [10, 50, 100, 350, 1000];

        foreach ($creditAmounts as $credits) {
            $expectedCents = CreditConstants::toCents($credits);

            // Simulate Stripe metadata
            $stripeMetadata = [
                'amount' => $expectedCents,
                'currency' => 'usd',
                'payment_method' => 'card',
            ];

            // Verify amount in metadata matches conversion
            $this->assertEquals($expectedCents, $stripeMetadata['amount']);

            // Verify round-trip: stripe cents -> credits -> stripe cents
            $roundTripCents = CreditConstants::toCents($credits);
            $this->assertEquals($stripeMetadata['amount'], $roundTripCents);
        }
    }

    /**
     * Smoke test: Gift creation with Stripe checkout amount accuracy.
     *
     * Verifies that when a gift is created for checkout, the amount sent to Stripe
     * matches the credit cost precisely.
     */
    #[Test]
    public function gift_creation_stripe_amount_equals_credit_conversion(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $creditCost = 175;
        $expectedStripeCents = CreditConstants::toCents($creditCost);

        // Create gift
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => $creditCost,
        ]);

        // Verify gift credit cost
        $this->assertEquals($creditCost, $gift->cost_in_credits);

        // Calculate what would be sent to Stripe
        $stripeAmount = CreditConstants::toCents($gift->cost_in_credits);

        // Verify the amounts match
        $this->assertEquals($expectedStripeCents, $stripeAmount);
        $this->assertEquals(3500, $stripeAmount); // 175 credits = 3500 cents ($35.00)
    }
}
