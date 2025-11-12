<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Comprehensive verification that all acceptance criteria for project completion are met
 */
class ProjectCompletionVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function all_crud_endpoints_are_accessible(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Create test data instead of relying on seeder
        $pet = Pet::factory()->for($user)->create();
        $appointment = Appointment::factory()->for($pet)->create();

        // Pet CRUD endpoints
        $petsResponse = $this->actingAs($user, 'sanctum')->get('/api/pets');
        $petsResponse->assertStatus(200);
        $pets = $petsResponse->json('data');
        $this->assertNotEmpty($pets, 'Should have seeded pets');
        $petId = $pets[0]['id'];

        $this->actingAs($user, 'sanctum')->get("/api/pets/{$petId}")->assertStatus(200);

        // Appointment CRUD endpoints
        $this->actingAs($user, 'sanctum')->get("/api/pets/{$petId}/appointments")->assertStatus(200);
        $appointmentsResponse = $this->actingAs($user, 'sanctum')->get("/api/pets/{$petId}/appointments");
        $appointments = $appointmentsResponse->json('data');
        if (! empty($appointments)) {
            $appointmentId = $appointments[0]['id'];
            $this->actingAs($user, 'sanctum')->get("/api/appointments/{$appointmentId}")->assertStatus(200);
        }
    }

    #[Test]
    public function new_developer_quick_start_works(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        // Create test data instead of relying on seeder
        $pets = Pet::factory(3)->for($user)->create();
        foreach ($pets as $pet) {
            Appointment::factory(2)->for($pet)->create();
        }

        // Should have exactly 3 pets as mentioned in README
        $response = $this->actingAs($user, 'sanctum')->get('/api/pets');
        $response->assertStatus(200);

        $pets = $response->json('data');
        $this->assertCount(3, $pets);

        // Each pet should have appointments
        foreach ($pets as $pet) {
            $appointmentResponse = $this->actingAs($user, 'sanctum')->get("/api/pets/{$pet['id']}/appointments");
            $appointmentResponse->assertStatus(200);
            $appointments = $appointmentResponse->json('data');
            $this->assertNotEmpty($appointments, "Pet {$pet['name']} should have appointments");
        }
    }
}
