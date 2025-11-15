<?php

namespace Tests\Feature\Credit;

use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for credit purchase functionality.
 */
class CreditPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected CreditBundle $bundle;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;
        $this->user->wallet()->create(['balance_credits' => 0]);

        $this->bundle = CreditBundle::create([
            'name' => 'Test Bundle',
            'credits' => 50,
            'price_cents' => 1000,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_purchase_credits(): void
    {
        $response = $this->postJson('/api/credits/purchase', [
            'credit_bundle_id' => $this->bundle->id,
            'return_url' => 'https://example.com/success',
        ]);

        $response->assertStatus(401);
    }

    public function test_credit_purchase_requires_valid_bundle(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/credits/purchase', [
                'credit_bundle_id' => 'invalid-uuid',
                'return_url' => 'https://example.com/success',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('credit_bundle_id');
    }

    public function test_credit_purchase_requires_return_url(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/credits/purchase', [
                'credit_bundle_id' => $this->bundle->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('return_url');
    }

    public function test_credit_purchase_cannot_use_inactive_bundle(): void
    {
        $inactiveBundle = CreditBundle::create([
            'name' => 'Inactive Bundle',
            'credits' => 100,
            'price_cents' => 2000,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/credits/purchase', [
                'credit_bundle_id' => $inactiveBundle->id,
                'return_url' => 'https://example.com/success',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('credit_bundle_id');
    }

    public function test_user_can_view_own_credit_purchase(): void
    {
        $purchase = CreditPurchase::create([
            'user_id' => $this->user->id,
            'wallet_id' => $this->user->wallet->id,
            'credit_bundle_id' => $this->bundle->id,
            'credits' => 50,
            'amount_cents' => 1000,
            'status' => 'pending',
            'stripe_session_id' => 'test_session_id',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/credits/{$purchase->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $purchase->id,
            'status' => 'pending',
            'credits' => 50,
        ]);
    }

    public function test_user_cannot_view_other_users_purchase(): void
    {
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $otherUser->wallet()->create(['balance_credits' => 0]);

        $purchase = CreditPurchase::create([
            'user_id' => $otherUser->id,
            'wallet_id' => $otherUser->wallet->id,
            'credit_bundle_id' => $this->bundle->id,
            'credits' => 50,
            'amount_cents' => 1000,
            'status' => 'pending',
            'stripe_session_id' => 'test_session_id',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/credits/{$purchase->id}");

        $response->assertStatus(403);
    }
}
