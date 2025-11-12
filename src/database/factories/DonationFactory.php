<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'pet_id' => Pet::factory(),
            'amount_cents' => $this->faker->numberBetween(100, 50000), // $1 to $500
            'stripe_session_id' => 'cs_test_' . Str::random(60),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'completed_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the donation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the donation is paid.
     */
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'paid',
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the donation failed.
     */
    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'failed',
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
