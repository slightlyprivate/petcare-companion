<?php

namespace Database\Factories;

use App\Constants\CreditConstants;
use App\Models\CreditBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditBundle>
 */
class CreditBundleFactory extends Factory
{
    protected $model = CreditBundle::class;

    public function definition(): array
    {
        $credits = $this->faker->randomElement([50, 100, 250, 500]);

        return [
            'name' => $this->faker->unique()->words(2, true),
            'credits' => $credits,
            'price_cents' => CreditConstants::toCents($credits),
            'is_active' => true,
        ];
    }
}
