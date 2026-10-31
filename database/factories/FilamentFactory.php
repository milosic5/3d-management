<?php

namespace Database\Factories;

use App\Models\Filament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Filament>
 */
class FilamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand' => fake()->company(),
            'name' => 'Filament '.fake()->unique()->bothify('??-###'),
            'material' => fake()->randomElement(['pla', 'petg']),
            'color_name' => fake()->safeColorName(),
            'color_hex' => fake()->hexColor(),
            'price_per_kg' => fake()->randomFloat(2, 1500, 4000),
            'empty_spool_weight_grams' => 200,
            'notes' => null,
            'stock_rolls' => fake()->numberBetween(0, 5),
        ];
    }

    public function material(string $material): static
    {
        return $this->state(fn (array $attributes) => [
            'material' => $material,
        ]);
    }
}
