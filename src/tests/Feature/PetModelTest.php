<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Pet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PetModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_pet_with_required_fields()
    {
        $pet = Pet::factory()->create([
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('pets', [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Doe',
        ]);

        $this->assertEquals('Buddy', $pet->name);
        $this->assertEquals('Dog', $pet->species);
        $this->assertEquals('John Doe', $pet->owner_name);
    }

    #[Test]
    public function it_can_calculate_pet_age_correctly()
    {
        $birthDate = Carbon::now()->subYears(3);

        $pet = Pet::factory()->create([
            'birth_date' => $birthDate,
        ]);

        $this->assertEquals(3, $pet->age);
    }

    #[Test]
    public function it_returns_null_age_when_birth_date_is_not_set()
    {
        $pet = Pet::factory()->create([
            'birth_date' => null,
        ]);

        $this->assertNull($pet->age);
    }

    #[Test]
    public function it_can_have_appointments()
    {
        $pet = Pet::factory()->create();
        $appointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
        ]);

        $this->assertTrue($pet->appointments()->exists());
        $this->assertEquals(1, $pet->appointments()->count());
        $this->assertEquals($pet->id, $appointment->pet_id);
    }

    #[Test]
    public function it_can_filter_upcoming_and_past_appointments()
    {
        $pet = Pet::factory()->create();

        // Create past appointment
        $pastAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->subDays(5),
        ]);

        // Create upcoming appointment
        $upcomingAppointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'scheduled_at' => Carbon::now()->addDays(5),
        ]);

        $this->assertEquals(1, $pet->upcomingAppointments()->count());
        $this->assertEquals(1, $pet->pastAppointments()->count());
        $this->assertTrue($pet->hasUpcomingAppointments());
        $this->assertEquals($upcomingAppointment->id, $pet->nextAppointment()->id);
    }

    #[Test]
    public function it_can_get_display_name_and_age_category()
    {
        $youngPet = Pet::factory()->create([
            'name' => 'Puppy',
            'species' => 'Dog',
            'birth_date' => Carbon::now()->subMonths(6),
        ]);

        $seniorPet = Pet::factory()->create([
            'name' => 'Old Dog',
            'species' => 'Dog',
            'birth_date' => Carbon::now()->subYears(10),
        ]);

        $this->assertEquals('Puppy (Dog)', $youngPet->display_name);
        $this->assertEquals('Baby', $youngPet->age_category);

        $this->assertEquals('Old Dog (Dog)', $seniorPet->display_name);
        $this->assertEquals('Senior', $seniorPet->age_category);
    }

    #[Test]
    public function it_can_use_query_scopes()
    {
        Pet::factory()->create(['species' => 'Dog', 'name' => 'Buddy', 'owner_name' => 'John Smith']);
        Pet::factory()->create(['species' => 'Cat', 'name' => 'Whiskers', 'owner_name' => 'Jane Doe']);
        Pet::factory()->create(['species' => 'Dog', 'name' => 'Rex', 'owner_name' => 'Bob Johnson']);

        $dogs = Pet::bySpecies('Dog')->get();
        $this->assertEquals(2, $dogs->count());

        $johnsAnimals = Pet::byOwner('John')->get();
        $this->assertEquals(2, $johnsAnimals->count());

        $buddies = Pet::byName('Buddy')->get();
        $this->assertEquals(1, $buddies->count());
    }
}
