<?php

namespace Database\Factories;

use App\Models\Packaging;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Packaging>
 */
class PackagingFactory extends Factory
{
    /**
     * Define the model's default state (a box).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'box',
            'name' => null,
            'length' => fake()->randomFloat(2, 5, 60),
            'width' => fake()->randomFloat(2, 5, 40),
            'height' => fake()->randomFloat(2, 2, 30),
            'stock' => fake()->numberBetween(0, 100),
        ];
    }

    public function envelope(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'envelope',
            'height' => null,
        ]);
    }
}
