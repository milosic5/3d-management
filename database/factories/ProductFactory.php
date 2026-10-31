<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'description' => null,
            // The product form no longer sends a material; it comes from the filament.
            'material' => null,
            'filament_id' => null,
            'color_name' => fake()->safeColorName(),
            'color_hex' => fake()->hexColor(),
            'weight_grams' => fake()->randomFloat(2, 5, 300),
            'print_time_minutes' => fake()->numberBetween(10, 600),
            'price' => fake()->randomFloat(2, 200, 5000),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
