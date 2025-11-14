<?php

namespace Tests\Feature;

use App\Models\Gift;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test suite for pet directory public listing endpoints.
 */
class PetDirectoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that public pets are returned in directory listing.
     */
    public function test_it_returns_public_pets_in_directory(): void
    {
        $publicPet = Pet::factory()->create(['is_public' => true]);
        $privatePet = Pet::factory()->create(['is_public' => false]);

        $response = $this->getJson('/api/public/pets');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json()['data']);
        $this->assertEquals($publicPet->id, $response->json()['data'][0]['id']);
    }

    /**
     * Test that directory includes gift metadata.
     */
    public function test_directory_includes_gift_metadata(): void
    {
        $pet = Pet::factory()->create(['is_public' => true]);
        Gift::factory(3)->create(['pet_id' => $pet->id, 'status' => 'paid', 'cost_in_credits' => 100]);
        Gift::factory()->create(['pet_id' => $pet->id, 'status' => 'failed']);

        $response = $this->getJson('/api/public/pets');

        $response->assertStatus(200);
        $petData = $response->json()['data'][0];

        $this->assertEquals(15000, $petData['total_gifts_cents']);
        $this->assertEquals(150.0, $petData['total_gifts']);
        $this->assertEquals(3, $petData['gift_count']);
    }

    /**
     * Test that directory avoids N+1 queries for gift aggregates.
     */
    public function test_directory_avoids_n_plus_one_queries(): void
    {
        // Create 5 public pets with gifts
        $pets = Pet::factory(5)->create(['is_public' => true]);
        foreach ($pets as $pet) {
            Gift::factory(3)->create(['pet_id' => $pet->id, 'status' => 'paid']);
        }

        // Baseline: Get count of queries executed
        DB::enableQueryLog();

        $response = $this->getJson('/api/public/pets');

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertStatus(200);
        $this->assertCount(5, $response->json()['data']);

        // Should have minimal queries:
        // 1. Select public pets
        // 2. Load gifts via eager loading (with)
        // 3. Count gifts aggregate (withCount)
        // 4. Sum gifts aggregate (withSum)
        // Expected: ~4 queries (pagination counts vary based on implementation)
        // Should NOT have 5 additional queries for each pet's gifts
        $this->assertLessThan(8, $queryCount, "Expected fewer than 8 queries but got {$queryCount}. N+1 query problem detected.");
    }

    /**
     * Test that directory can be sorted by popularity with eager loaded gifts.
     */
    public function test_directory_popularity_sorting_with_eager_loading(): void
    {
        $popularPet = Pet::factory()->create(['is_public' => true, 'name' => 'Popular Pet']);
        $unpopularPet = Pet::factory()->create(['is_public' => true, 'name' => 'Unpopular Pet']);

        Gift::factory(10)->create(['pet_id' => $popularPet->id, 'status' => 'paid']);
        Gift::factory(2)->create(['pet_id' => $unpopularPet->id, 'status' => 'paid']);

        $response = $this->getJson('/api/public/pets?sort_by=popularity&sort_direction=desc');

        $response->assertStatus(200);
        $data = $response->json()['data'];

        $this->assertEquals('Popular Pet', $data[0]['name']);
        $this->assertEquals('Unpopular Pet', $data[1]['name']);
    }

    /**
     * Test that directory filtering works with eager loaded gifts.
     */
    public function test_directory_filtering_with_eager_loading(): void
    {
        Pet::factory(3)->create(['is_public' => true, 'species' => 'Dog']);
        Pet::factory(2)->create(['is_public' => true, 'species' => 'Cat']);

        $response = $this->getJson('/api/public/pets?species=Dog');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json()['data']);
    }

    /**
     * Test that directory pagination with gifts works efficiently.
     */
    public function test_directory_pagination_with_gifts(): void
    {
        Pet::factory(15)->create(['is_public' => true]);

        $response = $this->getJson('/api/public/pets?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json()['data']);
        $this->assertEquals(2, $response->json()['meta']['last_page']);
    }

    /**
     * Test that gifts aggregate only counts paid gifts.
     */
    public function test_directory_only_counts_paid_gifts(): void
    {
        $pet = Pet::factory()->create(['is_public' => true]);

        Gift::factory(5)->create(['pet_id' => $pet->id, 'status' => 'paid', 'cost_in_credits' => 100]);
        Gift::factory(3)->create(['pet_id' => $pet->id, 'status' => 'pending']);
        Gift::factory(2)->create(['pet_id' => $pet->id, 'status' => 'failed']);

        $response = $this->getJson('/api/public/pets');

        $petData = $response->json()['data'][0];

        // Only paid gifts should be counted
        $this->assertEquals(5, $petData['gift_count']);
        $this->assertEquals(25000, $petData['total_gifts_cents']);
        $this->assertEquals(250.0, $petData['total_gifts']);
    }
}
