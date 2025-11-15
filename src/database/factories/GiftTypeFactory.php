<?php

namespace Database\Factories;

use App\Models\GiftType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftType>
 */
class GiftTypeFactory extends Factory
{
    protected $model = GiftType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;

        return [
            'name' => $this->faker->unique()->word().' '.++$counter,
            'description' => $this->faker->sentence(),
            'icon_emoji' => $this->faker->randomElement(['🎁', '🧸', '🍖', '🎀', '🛁']),
            'color_code' => '#'.substr(md5(rand()), 0, 6),
            'cost_in_credits' => $this->faker->numberBetween(10, 500),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * State: active gift type.
     */
    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State: inactive gift type.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
