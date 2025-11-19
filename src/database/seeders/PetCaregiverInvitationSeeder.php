<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\PetCaregiverInvitation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder for populating pet_caregiver_invitations with test data.
 *
 * @group Seeders
 */
class PetCaregiverInvitationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and pets
        $owner = User::where('email', 'owner@petcare.test')->first();
        $whiskers = Pet::where('name', 'Whiskers')->first();
        $charlie = Pet::where('name', 'Charlie')->first();

        if (! $owner || ! $whiskers || ! $charlie) {
            $this->command->warn('Required users or pets not found. Run DatabaseSeeder first.');

            return;
        }

        // Create a pending invitation for Whiskers
        PetCaregiverInvitation::create([
            'pet_id' => $whiskers->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'friend@example.com',
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        // Create an expired invitation for Charlie
        PetCaregiverInvitation::create([
            'pet_id' => $charlie->id,
            'inviter_id' => $owner->id,
            'invitee_email' => 'expired@example.com',
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->subDays(1),
        ]);

        // Create an accepted invitation for Buddy (caregiver from PetUserSeeder)
        $caregiver = User::where('email', 'caregiver@petcare.test')->first();
        $buddy = Pet::where('name', 'Buddy')->first();

        if ($caregiver && $buddy) {
            PetCaregiverInvitation::create([
                'pet_id' => $buddy->id,
                'inviter_id' => $owner->id,
                'invitee_email' => 'caregiver@petcare.test',
                'invitee_id' => $caregiver->id,
                'token' => Str::random(64),
                'status' => 'accepted',
                'expires_at' => now()->addDays(7),
                'accepted_at' => now()->subDays(2),
            ]);
        }

        $this->command->info('PetCaregiverInvitation records seeded successfully.');
    }
}
