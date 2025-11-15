<?php

namespace Tests\Feature\Credit;

use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditPurchaseHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_credit_purchases(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance_credits' => 0]);

        $bundle = CreditBundle::create([
            'name' => 'History Bundle',
            'credits' => 50,
            'price_cents' => 1000,
        ]);

        // Create purchases for this user
        CreditPurchase::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => 50,
            'amount_cents' => 1000,
            'status' => 'completed',
        ]);
        CreditPurchase::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => 50,
            'amount_cents' => 1000,
            'status' => 'pending',
        ]);

        // And a purchase for someone else
        $other = User::factory()->create();
        $other->wallet()->create(['balance_credits' => 0]);
        CreditPurchase::create([
            'user_id' => $other->id,
            'wallet_id' => $other->wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => 50,
            'amount_cents' => 1000,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/credits/purchases');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'wallet_id', 'credit_bundle_id', 'credits', 'amount_cents', 'amount_dollars', 'status', 'created_at'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        // Should only include the two purchases for this user
        $this->assertCount(2, $response->json('data'));
    }
}
