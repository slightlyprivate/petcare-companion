<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiResponseShapeVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        Pet::factory()->create([
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'owner_name' => 'John Smith',
            'birth_date' => '2020-01-15',
        ]);
    }

    #[Test]
    public function api_pets_list_returns_correct_response_shape()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory()->for($user)->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets');

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->has(
                        'links',
                        fn ($links) => $links->has('first')
                            ->has('last')
                            ->has('prev')
                            ->has('next')
                    )
                    ->has(
                        'meta',
                        fn ($meta) => $meta->has('current_page')
                            ->has('from')
                            ->has('last_page')
                            ->has('per_page')
                            ->has('to')
                            ->has('total')
                            ->etc()
                    )
                    ->has(
                        'data.0',
                        fn ($pet) => $pet->has('id')
                            ->has('name')
                            ->has('species')
                            ->has('breed')
                            ->has('birth_date')
                            ->has('owner_name')
                            ->has('age')
                            ->has('created_at')
                            ->has('updated_at')
                    )
                    ->etc()
            );

        // Verify no 500 errors and correct content type
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function api_pets_create_returns_correct_response_shape()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $petData = [
            'name' => 'Test Pet',
            'species' => 'Cat',
            'breed' => 'Persian',
            'birth_date' => '2021-06-15',
            'owner_name' => 'Test Owner',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->has(
                    'data',
                    fn ($pet) => $pet->has('id')
                        ->where('name', 'Test Pet')
                        ->where('species', 'Cat')
                        ->where('breed', 'Persian')
                        ->where('owner_name', 'Test Owner')
                        ->has('age')
                        ->has('created_at')
                        ->has('updated_at')
                        ->etc()
                )
                    ->etc()
            );

        // Verify no 500 errors and correct content type
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function api_pets_validation_errors_return_consistent_format()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', []);

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('message')
                    ->has(
                        'errors',
                        fn ($errors) => $errors->has('name')
                            ->has('species')
                            ->has('owner_name')
                            ->etc()
                    )
                    ->etc()
            );

        // Verify error messages are custom and user-friendly
        $errors = $response->json('errors');
        $this->assertStringContainsString('Pet name is required', $errors['name'][0]);
        $this->assertStringContainsString('Pet species is required', $errors['species'][0]);
        $this->assertStringContainsString('Owner name is required', $errors['owner_name'][0]);
    }

    #[Test]
    public function api_appointments_list_returns_correct_response_shape()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->for($user)->create();

        // Create test appointment
        $pet->appointments()->create([
            'title' => 'Test Appointment',
            'scheduled_at' => Carbon::tomorrow(),
            'notes' => 'Test notes',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments");

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('data')
                    ->has('links')
                    ->has('meta')
                    ->has(
                        'data.0',
                        fn ($appointment) => $appointment->has('id')
                            ->has('pet_id')
                            ->has('title')
                            ->has('scheduled_at')
                            ->has('scheduled_at_formatted')
                            ->has('notes')
                            ->has('is_upcoming')
                            ->has('created_at')
                            ->has('updated_at')
                            ->where('pet_id', $pet->id)
                            ->where('title', 'Test Appointment')
                    )
                    ->etc()
            );
    }

    #[Test]
    public function api_appointments_create_returns_correct_response_shape()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->for($user)->create();

        $appointmentData = [
            'title' => 'New Appointment',
            'scheduled_at' => '2025-12-15 14:30:00',
            'notes' => 'New appointment notes',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/pets/{$pet->id}/appointments", $appointmentData);

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->has(
                    'data',
                    fn ($appointment) => $appointment->has('id')
                        ->where('pet_id', $pet->id)
                        ->where('title', 'New Appointment')
                        ->where('notes', 'New appointment notes')
                        ->where('is_upcoming', true)
                        ->has('scheduled_at')
                        ->has('scheduled_at_formatted')
                        ->has('created_at')
                        ->has('updated_at')
                )
                    ->etc()
            );
    }

    #[Test]
    public function api_appointments_validation_errors_return_consistent_format()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/pets/{$pet->id}/appointments", [
            'scheduled_at' => 'invalid-date',
            // Missing required title
        ]);

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('message')
                    ->has(
                        'errors',
                        fn ($errors) => $errors->has('title')
                            ->has('scheduled_at')
                            ->etc()
                    )
                    ->etc()
            );

        // Verify error messages are custom
        $errors = $response->json('errors');
        $this->assertStringContainsString('title is required', $errors['title'][0]);
    }

    #[Test]
    public function api_404_errors_return_consistent_format()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets/999/appointments');

        $response->assertStatus(404)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('message')
                    ->etc()
            );
    }

    #[Test]
    public function api_pagination_includes_all_required_metadata()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Create multiple pets to test pagination
        Pet::factory(5)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets?per_page=2');

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has(
                    'meta',
                    fn ($meta) => $meta->whereType('current_page', 'integer')
                        ->whereType('from', ['integer', 'null'])
                        ->whereType('last_page', 'integer')
                        ->whereType('per_page', 'integer')
                        ->whereType('to', ['integer', 'null'])
                        ->whereType('total', 'integer')
                        ->where('per_page', 2)
                        ->where('current_page', 1)
                        ->etc()
                )
                    ->has(
                        'links',
                        fn ($links) => $links->has('first')
                            ->has('last')
                            ->has('prev')
                            ->has('next')
                    )
                    ->etc()
            );

        // Verify data count respects per_page
        $data = $response->json('data');
        $this->assertLessThanOrEqual(2, count($data));
    }

    #[Test]
    public function no_endpoints_return_500_errors()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Test all main endpoints to ensure no 500 errors
        $endpoints = [
            ['GET', '/api/pets'],
            ['POST', '/api/pets', ['name' => 'Test', 'species' => 'Dog', 'owner_name' => 'Owner']],
            ['GET', '/api/pets/1/appointments'],
            ['POST', '/api/pets/1/appointments', ['title' => 'Test', 'scheduled_at' => '2025-12-15 14:30:00']],
        ];

        foreach ($endpoints as $endpoint) {
            $method = $endpoint[0];
            $url = $endpoint[1];
            $data = $endpoint[2] ?? [];

            if ($method === 'GET') {
                $response = $this->actingAs($user, 'sanctum')->getJson($url);
            } else {
                $response = $this->actingAs($user, 'sanctum')->postJson($url, $data);
            }

            // Assert no 500 errors
            $this->assertNotEquals(
                500,
                $response->status(),
                "500 error on {$method} {$url}: ".$response->getContent()
            );

            // Assert proper content type
            $this->assertStringContainsString(
                'application/json',
                $response->headers->get('Content-Type', ''),
                "Wrong content type on {$method} {$url}"
            );
        }
    }
}
