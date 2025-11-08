<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\Appointment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create pets with varied data
        $pets = collect([
            Pet::factory()->dog()->create([
                'name' => 'Buddy',
                'owner_name' => 'John Smith',
                'breed' => 'Golden Retriever',
            ]),
            Pet::factory()->cat()->create([
                'name' => 'Whiskers',
                'owner_name' => 'Jane Doe',
                'breed' => 'Persian',
            ]),
            Pet::factory()->young()->create([
                'name' => 'Charlie',
                'species' => 'Dog',
                'breed' => 'Puppy Mix',
                'owner_name' => 'Bob Johnson',
            ]),
            Pet::factory()->senior()->create([
                'name' => 'Shadow',
                'species' => 'Cat',
                'breed' => 'Maine Coon',
                'owner_name' => 'Alice Brown',
            ]),
        ]);

        // Create additional random pets
        Pet::factory(6)->create();

        // Create appointments for each pet
        Pet::all()->each(function ($pet) {
            // Each pet gets 2-4 appointments
            $appointmentCount = rand(2, 4);
            
            // Mix of past and upcoming appointments
            Appointment::factory($appointmentCount)->create([
                'pet_id' => $pet->id,
            ]);
            
            // Ensure at least one upcoming appointment for demonstration
            if ($pet->appointments()->upcoming()->count() === 0) {
                Appointment::factory()->upcoming()->create([
                    'pet_id' => $pet->id,
                ]);
            }
        });
    }
}
