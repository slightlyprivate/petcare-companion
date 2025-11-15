<?php

namespace Tests\Feature\GiftType;

use App\Models\GiftType;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for gift type management and catalog.
 *
 * Tests cover:
 * - Public catalog access (no auth required)
 * - Admin CRUD operations (with auth required)
 * - Authorization (non-admin users blocked from write operations)
 * - Filtering (active/inactive status)
 * - Sorting (by sort_order)
 */
class GiftTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Public users can view all active gift types (catalog).
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function public_users_can_view_gift_types(): void
    {
        // Create some gift types
        GiftType::factory()->count(5)->create(['is_active' => true]);
        GiftType::factory()->create(['is_active' => false]); // This should not appear

        $response = $this->getJson('/api/public/gift-types');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data') // Only active ones
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'icon_emoji', 'color_code', 'sort_order', 'is_active'],
                ],
            ]);
    }

    /**
     * Test: Gift types are sorted by sort_order.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_types_are_sorted_by_sort_order(): void
    {
        GiftType::factory()->create(['name' => 'Type C', 'sort_order' => 3, 'is_active' => true]);
        GiftType::factory()->create(['name' => 'Type A', 'sort_order' => 1, 'is_active' => true]);
        GiftType::factory()->create(['name' => 'Type B', 'sort_order' => 2, 'is_active' => true]);

        $response = $this->getJson('/api/public/gift-types');

        $response->assertStatus(200);
        $names = array_map(fn ($item) => $item['name'], $response->json('data'));
        $this->assertEquals(['Type A', 'Type B', 'Type C'], $names);
    }

    /**
     * Test: Public users can view single gift type details.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function public_users_can_view_gift_type_details(): void
    {
        $giftType = GiftType::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/public/gift-types/{$giftType->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $giftType->id,
                    'name' => $giftType->name,
                    'is_active' => true,
                ],
            ]);
    }

    /**
     * Test: Admin can create gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_create_gift_type(): void
    {
        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/gift-types', [
                'name' => 'Premium Toy',
                'description' => 'Expensive toy for special occasions',
                'icon_emoji' => '🧸',
                'color_code' => '#FF6B6B',
                'sort_order' => 10,
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Premium Toy',
                    'icon_emoji' => '🧸',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('gift_types', [
            'name' => 'Premium Toy',
            'icon_emoji' => '🧸',
        ]);
    }

    /**
     * Test: Non-admin users cannot create gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_users_cannot_create_gift_type(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/gift-types', [
                'name' => 'New Gift Type',
                'icon_emoji' => '🎁',
                'color_code' => '#FF6B6B',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test: Unauthenticated users cannot create gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function unauthenticated_users_cannot_create_gift_type(): void
    {
        $response = $this->postJson('/api/gift-types', [
            'name' => 'New Gift Type',
            'icon_emoji' => '🎁',
            'color_code' => '#FF6B6B',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Gift type validation - duplicate names rejected.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function duplicate_gift_type_names_rejected(): void
    {
        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        GiftType::factory()->create(['name' => 'Toy']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/gift-types', [
                'name' => 'Toy', // Duplicate
                'icon_emoji' => '🧸',
                'color_code' => '#FF6B6B',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test: Gift type validation - invalid color code rejected.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function invalid_color_code_rejected(): void
    {
        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/gift-types', [
                'name' => 'New Type',
                'icon_emoji' => '🎁',
                'color_code' => 'INVALID', // Not hex format
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('color_code');
    }

    /**
     * Test: Admin can update gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_update_gift_type(): void
    {
        $giftType = GiftType::factory()->create();

        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        $giftType = GiftType::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/gift-types/{$giftType->id}", [
                'name' => 'New Name',
                'description' => 'Updated description',
                'is_active' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'New Name',
                    'description' => 'Updated description',
                    'is_active' => false,
                ],
            ]);

        $this->assertDatabaseHas('gift_types', [
            'id' => $giftType->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test: Non-admin users cannot update gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_users_cannot_update_gift_type(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $giftType = GiftType::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/gift-types/{$giftType->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test: Admin can delete gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_delete_gift_type(): void
    {
        $giftType = GiftType::factory()->create();

        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        $giftType = GiftType::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/gift-types/{$giftType->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('gift_types', [
            'id' => $giftType->id,
        ]);
    }

    /**
     * Test: Non-admin users cannot delete gift type.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_users_cannot_delete_gift_type(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $giftType = GiftType::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/gift-types/{$giftType->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: Gift type can be assigned to gifts and retrieved.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_type_relationship_works(): void
    {
        $giftType = GiftType::factory()->create(['name' => 'Premium Toy']);

        // Create a gift with this type (manually, as gift creation through API is complex)
        $gift = \App\Models\Gift::factory()->create([
            'gift_type_id' => $giftType->id,
        ]);

        // Verify relationship
        $this->assertEquals($giftType->id, $gift->giftType->id);
        $this->assertEquals('Premium Toy', $gift->giftType->name);
    }

    /**
     * Test: Inactive gift types don't appear in public catalog.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function inactive_gift_types_excluded_from_catalog(): void
    {
        GiftType::factory()->create(['is_active' => true, 'name' => 'Active']);
        GiftType::factory()->create(['is_active' => false, 'name' => 'Inactive']);

        $response = $this->getJson('/api/public/gift-types');

        $response->assertStatus(200);
        $names = array_map(fn ($item) => $item['name'], $response->json('data'));
        $this->assertContains('Active', $names);
        $this->assertNotContains('Inactive', $names);
    }
}
