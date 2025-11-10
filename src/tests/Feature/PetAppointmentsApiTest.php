<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PetAppointmentsApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_appointments_for_specific_pet()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet1 = Pet::factory()->create();
        $pet2 = Pet::factory()->create();

        // Create appointments for pet1
        $appointments1 = Appointment::factory(3)->create([
            'pet_id' => $pet1->id,
        ]);

        // Create appointments for pet2
        $appointments2 = Appointment::factory(2)->create([
            'pet_id' => $pet2->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet1->id}/appointments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
                'links',
                'meta',
            ]);

        // Should only return appointments for pet1
        $this->assertCount(3, $response->json('data'));

        foreach ($response->json('data') as $appointment) {
            $this->assertEquals($pet1->id, $appointment['pet_id']);
        }
    }

    #[Test]
    public function it_returns_404_for_non_existent_pet()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets/999/appointments');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_filter_appointments_by_status()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create upcoming appointment
        $upcomingAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->addDays(3),
        ]);

        // Create past appointment
        $pastAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->subDays(3),
        ]);

        // Test upcoming filter
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?status=upcoming");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($upcomingAppointment->id, $response->json('data.0.id'));

        // Test past filter
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?status=past");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($pastAppointment->id, $response->json('data.0.id'));
    }

    #[Test]
    public function it_can_filter_appointments_by_today()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create today's appointment
        $todayAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::today()->setHour(14),
        ]);

        // Create other appointments
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::yesterday(),
        ]);

        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::tomorrow(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?status=today");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($todayAppointment->id, $response->json('data.0.id'));
    }

    #[Test]
    public function it_can_filter_appointments_by_date_range()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(7);

        // Appointment within range
        $appointmentInRange = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => $startDate->copy()->addDays(2),
        ]);

        // Appointment before range
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => $startDate->copy()->subDays(1),
        ]);

        // Appointment after range
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => $endDate->copy()->addDays(1),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?from_date={$startDate->toDateString()}&to_date={$endDate->toDateString()}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($appointmentInRange->id, $response->json('data.0.id'));
    }

    #[Test]
    public function it_can_search_appointments_by_title_or_notes()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $appointment1 = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Wellness Check',
            'notes' => 'Annual checkup',
        ]);

        $appointment2 = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Vaccination',
            'notes' => 'Rabies and wellness shots',
        ]);

        $appointment3 = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Grooming',
            'notes' => 'Basic grooming session',
        ]);

        // Search by title
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?search=Check");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($appointment1->id, $response->json('data.0.id'));

        // Search by notes
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?search=grooming");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data')); // Only appointment3 contains "grooming"
    }

    #[Test]
    public function it_can_sort_appointments()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $appointment1 = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Zebra Appointment',
            'scheduled_at' => Carbon::now()->addDays(1),
        ]);

        $appointment2 = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Alpha Appointment',
            'scheduled_at' => Carbon::now()->addDays(3),
        ]);

        $appointment3 = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Beta Appointment',
            'scheduled_at' => Carbon::now()->addDays(2),
        ]);

        // Sort by title ascending
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?sort_by=title&sort_direction=asc");
        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertEquals(['Alpha Appointment', 'Beta Appointment', 'Zebra Appointment'], $titles);

        // Sort by scheduled_at descending
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?sort_by=scheduled_at&sort_direction=desc");
        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals([$appointment2->id, $appointment3->id, $appointment1->id], $ids);
    }

    #[Test]
    public function it_paginates_appointments()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        Appointment::factory(25)->create([
            'pet_id' => $pet->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?per_page=10");

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    #[Test]
    public function it_returns_empty_collection_for_pet_with_no_appointments()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments");

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function it_can_combine_multiple_filters()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create appointments with different combinations
        $targetAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Wellness Check',
            'scheduled_at' => Carbon::now()->addDays(5),
            'notes' => 'Important checkup',
        ]);

        // This should be filtered out by status
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Wellness Check',
            'scheduled_at' => Carbon::now()->subDays(2),
        ]);

        // This should be filtered out by search
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Grooming',
            'scheduled_at' => Carbon::now()->addDays(3),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/pets/{$pet->id}/appointments?status=upcoming&search=Wellness");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($targetAppointment->id, $response->json('data.0.id'));
    }
}
