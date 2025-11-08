<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AppointmentModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_appointment_for_a_pet()
    {
        $pet = Pet::factory()->create();
        $scheduledTime = Carbon::now()->addDays(7);
        
        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'title' => 'Wellness Check',
            'scheduled_at' => $scheduledTime,
            'notes' => 'Annual checkup',
        ]);

        $this->assertDatabaseHas('appointments', [
            'pet_id' => $pet->id,
            'title' => 'Wellness Check',
            'notes' => 'Annual checkup',
        ]);

        $this->assertEquals($pet->id, $appointment->pet->id);
    }

    /** @test */
    public function it_can_determine_if_appointment_is_upcoming_or_past()
    {
        $pet = Pet::factory()->create();
        
        $upcomingAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->addDays(3),
        ]);
        
        $pastAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->subDays(3),
        ]);

        $this->assertTrue($upcomingAppointment->isUpcoming());
        $this->assertFalse($upcomingAppointment->isOverdue());
        $this->assertEquals('upcoming', $upcomingAppointment->status);
        
        $this->assertFalse($pastAppointment->isUpcoming());
        $this->assertTrue($pastAppointment->isOverdue());
        $this->assertEquals('completed', $pastAppointment->status);
    }

    /** @test */
    public function it_can_use_query_scopes()
    {
        $pet = Pet::factory()->create();
        
        // Create appointments at different times
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->subDays(5), // Past
        ]);
        
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->addDays(5), // Future
        ]);
        
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::today()->setHour(14), // Today
        ]);

        $this->assertEquals(1, Appointment::upcoming()->count());
        $this->assertEquals(2, Appointment::past()->count()); // Past + Today's past time
        $this->assertEquals(1, Appointment::today()->count());
    }

    /** @test */
    public function it_can_get_time_until_appointment()
    {
        $pet = Pet::factory()->create();
        
        $futureAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->addDays(3),
        ]);
        
        $pastAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->subDays(3),
        ]);

        $this->assertNotNull($futureAppointment->time_until);
        $this->assertStringContainsString('days', $futureAppointment->time_until);
        
        $this->assertNull($pastAppointment->time_until);
    }

    /** @test */
    public function it_can_filter_appointments_by_week()
    {
        $pet = Pet::factory()->create();
        
        // This week
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->startOfWeek()->addDays(2),
        ]);
        
        // Next week
        Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->addWeek(),
        ]);

        $this->assertEquals(1, Appointment::thisWeek()->count());
    }
}