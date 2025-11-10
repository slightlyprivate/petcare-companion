<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointmentTypes = [
            'Wellness Check-up',
            'Vaccination',
            'Dental Cleaning',
            'Grooming',
            'Surgery Consultation',
            'Follow-up Visit',
            'Emergency Check',
            'Behavior Consultation',
            'Nail Trimming',
            'Spay/Neuter Consultation',
            'Microchip Installation',
            'Health Certificate',
            'Parasite Treatment',
            'Blood Work',
            'X-Ray Examination',
        ];

        $notes = [
            'Regular check-up scheduled',
            'Follow-up for previous treatment',
            'Owner concerned about recent behavior changes',
            'Due for annual vaccinations',
            'Routine dental cleaning',
            'Pre-surgery consultation required',
            'Monitoring ongoing treatment',
            null, // Some appointments might not have notes
            'Emergency visit requested',
            'Preventive care appointment',
        ];

        return [
            'pet_id' => Pet::factory(),
            'title' => $this->faker->randomElement($appointmentTypes),
            'scheduled_at' => $this->faker->dateTimeBetween('-1 month', '+3 months'),
            'notes' => $this->faker->randomElement($notes),
        ];
    }

    /**
     * Indicate that the appointment is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+3 months'),
        ]);
    }

    /**
     * Indicate that the appointment is in the past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Set the appointment as an emergency visit.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Emergency Visit',
            'notes' => 'Urgent medical attention required',
        ]);
    }

    /**
     * Set the appointment as a routine check-up.
     */
    public function routine(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Routine Wellness Check-up',
            'notes' => 'Annual wellness examination',
        ]);
    }
}
