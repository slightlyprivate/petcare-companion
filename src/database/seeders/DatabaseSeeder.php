<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder for populating the database with initial data.
 *
 * @group Seeders
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->administrator()
            ->create([
                'email' => 'admin@petcare.test',
            ]);

        $owner = User::factory()->create([
            'email' => 'owner@petcare.test',
        ]);

        // Create exactly 3 predictable pets for demonstration
        $pets = collect([
            Pet::factory()->for($owner)->create([
                'name' => 'Buddy',
                'species' => 'Dog',
                'breed' => 'Golden Retriever',
                'owner_name' => 'John Smith',
                'birth_date' => '2019-03-15', // ~6 years old
            ]),
            Pet::factory()->for($owner)->create([
                'name' => 'Whiskers',
                'species' => 'Cat',
                'breed' => 'Persian',
                'owner_name' => 'Jane Doe',
                'birth_date' => '2020-07-22', // ~5 years old
            ]),
            Pet::factory()->for($owner)->create([
                'name' => 'Charlie',
                'species' => 'Dog',
                'breed' => 'Labrador',
                'owner_name' => 'Bob Johnson',
                'birth_date' => '2023-01-10', // ~2 years old
            ]),
        ]);

        // Create 1-2 future appointments per pet for predictable demo data
        $pets->each(function ($pet, $index) {
            $futureAppointments = $index === 0 ? 2 : 1; // Buddy gets 2, others get 1

            for ($i = 0; $i < $futureAppointments; $i++) {
                Appointment::factory()->create([
                    'pet_id' => $pet->id,
                    'title' => $this->getAppointmentTitle($pet->species, $i),
                    'scheduled_at' => now()->addDays(7 + ($i * 14)), // 1 week, 3 weeks, etc.
                    'notes' => $this->getAppointmentNotes($pet->name, $i),
                ]);
            }
        });

        // Seed pet-user relationships and caregiver invitations
        $this->call([
            PetUserSeeder::class,
            PetCaregiverInvitationSeeder::class,
        ]);
    }

    /**
     * Get predictable appointment titles based on species and index.
     */
    private function getAppointmentTitle(string $species, int $index): string
    {
        $dogAppointments = ['Wellness Check', 'Vaccination Update'];
        $catAppointments = ['Annual Checkup', 'Dental Cleaning'];

        $appointments = $species === 'Dog' ? $dogAppointments : $catAppointments;

        return $appointments[$index] ?? 'Follow-up Visit';
    }

    /**
     * Get predictable appointment notes.
     */
    private function getAppointmentNotes(string $petName, int $index): string
    {
        $notes = [
            "Routine health examination for {$petName}. Check weight, vaccinations, and overall health.",
            "Follow-up appointment for {$petName}. Review previous visit and discuss any concerns.",
        ];

        return $notes[$index] ?? "General appointment for {$petName}.";
    }
}
