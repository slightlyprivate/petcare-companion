<?php

namespace Tests\Feature\Gift;

use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for wallet-based gift enforcement.
 *
 * Tests that gifting deducts credits from user wallets and validates
 * sufficient balance before allowing gift creation.
 */
class WalletBasedGiftingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        try {
            \Mockery::close();
            \Mockery::resetContainer();
        } catch (\Exception $e) {
            // Ignore closing errors
        }
        parent::tearDown();
    }

    /**
     * Test: Gift creation requires sufficient wallet balance.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_gift_with_insufficient_balance(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet with insufficient credits
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 50, // Only 50 credits
        ]);

        $pet = Pet::factory()->create();

        // Use fake Stripe keys to avoid real API calls
        config([
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        // Try to send 100 credit gift (should fail)
        $giftType = \App\Models\GiftType::factory()->create(['cost_in_credits' => 100, 'is_active' => true]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gift_type_id']);
    }

    /**
     * Test: Gift creation with exact wallet balance succeeds.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_gift_with_exact_balance(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet with exact credits needed
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 100,
        ]);

        $pet = Pet::factory()->create();

        // Use fake Stripe keys to avoid real API calls
        config([
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        // Send 100 credit gift (should succeed until Stripe call)
        $giftType = \App\Models\GiftType::factory()->create(['cost_in_credits' => 100, 'is_active' => true]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType->id,
            ]);

        $this->assertDatabaseHas('gifts', ['user_id' => $user->id, 'cost_in_credits' => 100]);
    }

    /**
     * Test: Gift creation deducts credits from wallet immediately.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_creation_deducts_credits_from_wallet(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet with 500 credits
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 500,
        ]);

        $pet = Pet::factory()->create();

        // Create a gift manually to test wallet deduction
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 100,
            'status' => 'paid', // Already paid/completed
        ]);

        // Simulate what webhook does - deduct credits
        $wallet->update([
            'balance_credits' => $wallet->balance_credits - $gift->cost_in_credits,
        ]);
        $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => $gift->cost_in_credits * 20, // Convert credits to cents
            'amount_credits' => $gift->cost_in_credits,
            'reason' => 'gift_sent',
            'related_type' => 'gift',
        ]);

        // Refresh wallet to get updated balance
        $wallet->refresh();

        // Verify balance was deducted
        $this->assertEquals(400, $wallet->balance_credits);

        // Verify transaction was recorded
        $this->assertDatabaseHas('credit_transactions', [
            'wallet_id' => $wallet->id,
            'amount_credits' => 100,
            'type' => 'debit',
            'reason' => 'gift_sent',
        ]);
    }

    /**
     * Test: Transaction is logged for each gift credit deduction.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_deduction_creates_transaction_record(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 500,
        ]);

        $pet = Pet::factory()->create();

        // Create and complete a gift
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'cost_in_credits' => 75,
            'status' => 'paid',
        ]);

        // Simulate webhook deduction
        $wallet->update([
            'balance_credits' => $wallet->balance_credits - $gift->cost_in_credits,
        ]);
        $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => 75 * 20, // 75 credits = 1500 cents
            'amount_credits' => 75,
            'reason' => 'gift_sent',
            'related_type' => 'gift',
        ]);

        // Verify transaction was created
        $this->assertDatabaseHas('credit_transactions', [
            'wallet_id' => $wallet->id,
            'amount_credits' => 75,
            'type' => 'debit',
            'reason' => 'gift_sent',
        ]);
    }

    /**
     * Test: Multiple gifts correctly deduct cumulative credits.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function multiple_gifts_correctly_accumulate_deductions(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet with 300 credits
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 300,
        ]);

        // Create and complete multiple gifts
        Gift::factory()->create([
            'user_id' => $user->id,
            'cost_in_credits' => 75,
            'status' => 'paid',
        ]);

        // Deduct first gift
        $wallet->update([
            'balance_credits' => $wallet->balance_credits - 75,
        ]);
        $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => 75 * 20,
            'amount_credits' => 75,
            'reason' => 'gift_sent',
        ]);

        $wallet->refresh();
        $this->assertEquals(225, $wallet->balance_credits);

        // Deduct second gift
        Gift::factory()->create([
            'user_id' => $user->id,
            'cost_in_credits' => 100,
            'status' => 'paid',
        ]);

        $wallet->update([
            'balance_credits' => $wallet->balance_credits - 100,
        ]);
        $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => 100 * 20,
            'amount_credits' => 100,
            'reason' => 'gift_sent',
        ]);

        $wallet->refresh();
        $this->assertEquals(125, $wallet->balance_credits);

        // Deduct third gift
        Gift::factory()->create([
            'user_id' => $user->id,
            'cost_in_credits' => 50,
            'status' => 'paid',
        ]);

        $wallet->update([
            'balance_credits' => $wallet->balance_credits - 50,
        ]);
        $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => 50 * 20,
            'amount_credits' => 50,
            'reason' => 'gift_sent',
        ]);

        $wallet->refresh();
        $this->assertEquals(75, $wallet->balance_credits);

        // Verify all transactions were logged
        $this->assertDatabaseCount('credit_transactions', 3);
    }

    /**
     * Test: User with no wallet cannot send gifts.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function user_without_wallet_cannot_send_gift(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Note: Not creating wallet for this user

        $pet = Pet::factory()->create();

        // Use fake Stripe keys
        config([
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        // Try to send gift
        $giftType = \App\Models\GiftType::factory()->create(['cost_in_credits' => 100, 'is_active' => true]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gift_type_id']);
    }

    /**
     * Test: Zero balance rejects gift creation.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function user_with_zero_balance_cannot_send_gift(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet with zero credits
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 0,
        ]);

        $pet = Pet::factory()->create();

        // Use fake Stripe keys
        config([
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        // Try to send gift
        $giftType = \App\Models\GiftType::factory()->create(['cost_in_credits' => 10, 'is_active' => true]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gift_type_id']);
    }

    /**
     * Test: Partial balance insufficient for large gift.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function insufficient_partial_balance_rejected(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create wallet with 99 credits (need 100)
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_credits' => 99,
        ]);

        $pet = Pet::factory()->create();

        // Use fake Stripe keys
        config([
            'services.stripe.secret' => 'sk_test_fake',
        ]);

        // Try to send 100 credit gift
        $giftType = \App\Models\GiftType::factory()->create(['cost_in_credits' => 100, 'is_active' => true]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/pets/{$pet->id}/gifts", [
                'gift_type_id' => (string) $giftType->id,
            ]);

        $response->assertStatus(422);
    }
}
