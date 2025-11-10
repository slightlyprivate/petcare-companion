<?php

namespace Database\Factories;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Pet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $species = $this->faker->randomElement(['Dog', 'Cat', 'Bird', 'Fish', 'Rabbit', 'Guinea Pig', 'Hamster', 'Turtle']);

        $breeds = [
            'Dog' => ['Labrador', 'Golden Retriever', 'German Shepherd', 'Bulldog', 'Poodle', 'Beagle', 'Rottweiler', 'Yorkshire Terrier'],
            'Cat' => ['Persian', 'Maine Coon', 'Siamese', 'Ragdoll', 'British Shorthair', 'Abyssinian', 'Russian Blue', 'Bengal'],
            'Bird' => ['Parrot', 'Canary', 'Budgie', 'Cockatiel', 'Finch', 'Lovebird', 'Conure', 'Macaw'],
            'Fish' => ['Goldfish', 'Betta', 'Guppy', 'Angelfish', 'Neon Tetra', 'Clownfish', 'Molly', 'Swordtail'],
            'Rabbit' => ['Holland Lop', 'Netherland Dwarf', 'Mini Rex', 'Lionhead', 'Flemish Giant', 'Angora', 'Dutch', 'New Zealand'],
            'Guinea Pig' => ['American', 'Abyssinian', 'Peruvian', 'Silkie', 'Teddy', 'Skinny Pig', 'Texel', 'Rex'],
            'Hamster' => ['Syrian', 'Dwarf Campbell', 'Dwarf Winter White', 'Roborovski', 'Chinese', 'European'],
            'Turtle' => ['Red-eared Slider', 'Box Turtle', 'Painted Turtle', 'Russian Tortoise', 'Hermann\'s Tortoise'],
        ];

        $breed = $this->faker->randomElement($breeds[$species] ?? ['Mixed']);

        return [
            'name' => $this->faker->firstName(),
            'species' => $species,
            'breed' => $breed,
            'birth_date' => $this->faker->dateTimeBetween('-15 years', '-2 months')->format('Y-m-d'),
            'owner_name' => $this->faker->name(),
        ];
    }

    /**
     * Indicate that the pet is a puppy/kitten (young).
     */
    public function young(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-2 years', '-2 months')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the pet is a senior.
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-15 years', '-8 years')->format('Y-m-d'),
        ]);
    }

    /**
     * Set the pet species to dog.
     */
    public function dog(): static
    {
        return $this->state(fn (array $attributes) => [
            'species' => 'Dog',
            'breed' => $this->faker->randomElement(['Labrador', 'Golden Retriever', 'German Shepherd', 'Bulldog', 'Poodle', 'Beagle']),
        ]);
    }

    /**
     * Set the pet species to cat.
     */
    public function cat(): static
    {
        return $this->state(fn (array $attributes) => [
            'species' => 'Cat',
            'breed' => $this->faker->randomElement(['Persian', 'Maine Coon', 'Siamese', 'Ragdoll', 'British Shorthair', 'Abyssinian']),
        ]);
    }
}
