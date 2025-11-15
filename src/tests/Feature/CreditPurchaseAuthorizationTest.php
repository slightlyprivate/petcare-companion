<?php

namespace Tests\Feature;

use App\Models\CreditPurchase;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditPurchaseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_others_credit_purchase(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $intruder */
        $intruder = User::factory()->create();

        /** @var CreditPurchase $purchase */
        $purchase = CreditPurchase::factory()->create();
        // Ensure the purchase belongs to $owner
        $purchase->update(['user_id' => $owner->id, 'wallet_id' => Wallet::factory()->create(['user_id' => $owner->id])->id]);

        // Owner can view
        $this->actingAs($owner, 'sanctum')
            ->getJson("/api/credits/{$purchase->id}")
            ->assertOk()
            ->assertJsonPath('purchase.id', (string) $purchase->id);

        // Other user forbidden
        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/credits/{$purchase->id}")
            ->assertForbidden();
    }

    public function test_index_returns_only_authenticated_users_purchases(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $other = User::factory()->create();

        // Create two purchases for user and one for other
        $p1 = CreditPurchase::factory()->create();
        $p1->update(['user_id' => $user->id, 'wallet_id' => Wallet::factory()->create(['user_id' => $user->id])->id]);

        $p2 = CreditPurchase::factory()->create();
        $p2->update(['user_id' => $user->id, 'wallet_id' => Wallet::factory()->create(['user_id' => $user->id])->id]);

        $otherPurchase = CreditPurchase::factory()->create();
        $otherPurchase->update(['user_id' => $other->id, 'wallet_id' => Wallet::factory()->create(['user_id' => $other->id])->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/credits/purchases');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($p1->id, $ids);
        $this->assertContains($p2->id, $ids);
        $this->assertNotContains($otherPurchase->id, $ids);
    }
}
