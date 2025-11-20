<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetActivityListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_paginated_activities_with_default_per_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->getKey()]);

        // Create 20 activities
        for ($i = 0; $i < 20; $i++) {
            PetActivity::create([
                'pet_id' => $pet->getKey(),
                'user_id' => $user->getKey(),
                'type' => 'feeding',
                'description' => 'Activity ' . $i,
            ]);
        }

        $response = $this->actingAs($user)->getJson('/api/pets/' . $pet->getKey() . '/activities');
        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonCount(15, 'data');
    }

    public function test_filters_by_type(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->getKey()]);

        PetActivity::create(['pet_id' => $pet->getKey(), 'user_id' => $user->getKey(), 'type' => 'feeding', 'description' => 'Breakfast']);
        PetActivity::create(['pet_id' => $pet->getKey(), 'user_id' => $user->getKey(), 'type' => 'walk', 'description' => 'Morning walk']);
        PetActivity::create(['pet_id' => $pet->getKey(), 'user_id' => $user->getKey(), 'type' => 'feeding', 'description' => 'Dinner']);

        $response = $this->actingAs($user)->getJson('/api/pets/' . $pet->getKey() . '/activities?type=feeding');
        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_filters_by_date_range(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->getKey()]);

        // Older activity (5 days ago)
        $old = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'feeding',
            'description' => 'Old feeding',
        ]);
        $old->created_at = now()->subDays(5);
        $old->updated_at = now()->subDays(5);
        $old->save();

        // Recent activities
        $recent1 = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'walk',
            'description' => 'Recent walk',
        ]);
        $recent1->created_at = now()->subDays(2);
        $recent1->updated_at = now()->subDays(2);
        $recent1->save();

        $recent2 = PetActivity::create([
            'pet_id' => $pet->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'feeding',
            'description' => 'Recent feeding',
        ]);
        $recent2->created_at = now()->subDay();
        $recent2->updated_at = now()->subDay();
        $recent2->save();

        $from = now()->subDays(3)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $response = $this->actingAs($user)->getJson('/api/pets/' . $pet->getKey() . '/activities?date_from=' . $from . '&date_to=' . $to);
        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }
}
