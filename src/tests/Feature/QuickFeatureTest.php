<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuickFeatureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_pets_happy_path()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Arrange: Create some pets
        $pets = Pet::factory(3)->for($user)->create([
            'species' => 'Dog',
            'owner_name' => 'John Smith',
        ]);

        // Act: Make request to list pets
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets');

        // Assert: Check response structure and data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'species',
                        'breed',
                        'birth_date',
                        'owner_name',
                        'age',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'total',
                ],
            ]);

        // Verify we got all 3 pets
        $this->assertCount(3, $response->json('data'));

        // Verify specific data structure
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('data.0.species', 'Dog')
                ->where('data.0.owner_name', 'John Smith')
                ->has('data.0.name')
                ->has('data.0.id')
                ->etc()
        );
    }

    #[Test]
    public function it_can_create_appointment_happy_path()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Arrange: Create a pet to add appointment to
        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
        ]);

        $appointmentData = [
            'title' => 'Wellness Check',
            'scheduled_at' => '2025-12-15 14:30:00',
            'notes' => 'Annual health examination and vaccinations',
        ];

        // Act: Make request to create appointment
        $response = $this->actingAs($user, 'sanctum')->postJson("/api/pets/{$pet->id}/appointments", $appointmentData);

        // Assert: Check response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'pet_id',
                    'title',
                    'scheduled_at',
                    'scheduled_at_formatted',
                    'notes',
                    'is_upcoming',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Verify the data matches what we sent
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('data.pet_id', $pet->id)
                ->where('data.title', 'Wellness Check')
                ->where('data.notes', 'Annual health examination and vaccinations')
                ->where('data.is_upcoming', true)
                ->has('data.id')
                ->etc()
        );

        // Verify appointment was actually created in database
        $this->assertDatabaseHas('appointments', [
            'pet_id' => $pet->id,
            'title' => 'Wellness Check',
            'notes' => 'Annual health examination and vaccinations',
        ]);

        // Verify the pet now has 1 appointment
        $this->assertEquals(1, $pet->fresh()->appointments()->count());
    }
}
