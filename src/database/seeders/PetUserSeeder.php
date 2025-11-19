<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\PetUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder for populating pet_user relationships with test data.
 *
 * @group Seeders
 */
class PetUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and pets
        $owner = User::where('email', 'owner@petcare.test')->first();
        $buddy = Pet::where('name', 'Buddy')->first();
        $whiskers = Pet::where('name', 'Whiskers')->first();
        $charlie = Pet::where('name', 'Charlie')->first();

        if (! $owner || ! $buddy || ! $whiskers || ! $charlie) {
            $this->command->warn('Required users or pets not found. Run DatabaseSeeder first.');

            return;
        }

        // Create owner relationships for all three pets
        PetUser::create([
            'pet_id' => $buddy->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        PetUser::create([
            'pet_id' => $whiskers->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        PetUser::create([
            'pet_id' => $charlie->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        // Create a caregiver user
        $caregiver = User::factory()->create([
            'email' => 'caregiver@petcare.test',
        ]);

        // Add caregiver for Buddy
        PetUser::create([
            'pet_id' => $buddy->id,
            'user_id' => $caregiver->id,
            'role' => 'caregiver',
        ]);

        $this->command->info('PetUser relationships seeded successfully.');
    }
}
