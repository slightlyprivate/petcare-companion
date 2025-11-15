<?php

namespace Tests\Feature;

use App\Models\GiftType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftTypeAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_store_gift_type(): void
    {
        /** @var User $admin */
        $admin = User::factory()->administrator()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $payload = [
            'name' => 'Premium Toy',
            'description' => 'High quality toy',
            'icon_emoji' => '🎁',
            'color_code' => '#FFAA00',
            'cost_in_credits' => 100,
            'is_active' => true,
        ];

        // Admin can create
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/gift-types', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Premium Toy');

        // Non-admin forbidden
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/gift-types', $payload)
            ->assertForbidden();
    }

    public function test_only_admin_can_update_gift_type(): void
    {
        /** @var User $admin */
        $admin = User::factory()->administrator()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $giftType = GiftType::factory()->create(['name' => 'Basic Toy']);

        // Admin can update
        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/gift-types/{$giftType->id}", ['name' => 'Updated Toy'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Toy');

        // Non-admin forbidden
        $this->actingAs($user, 'sanctum')
            ->putJson("/api/gift-types/{$giftType->id}", ['name' => 'Should Fail'])
            ->assertForbidden();
    }

    public function test_only_admin_can_destroy_gift_type(): void
    {
        /** @var User $admin */
        $admin = User::factory()->administrator()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $giftType = GiftType::factory()->create();

        // Non-admin forbidden
        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/gift-types/{$giftType->id}")
            ->assertForbidden();

        // Admin can delete
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/gift-types/{$giftType->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('gift_types', ['id' => $giftType->id]);
    }
}
