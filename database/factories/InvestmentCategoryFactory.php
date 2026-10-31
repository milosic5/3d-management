<?php

namespace Database\Factories;

use App\Models\InvestmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentCategory>
 */
class InvestmentCategoryFactory extends Factory
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
            'type' => fake()->randomElement(['equipment', 'consumable', 'utility', 'other']),
        ];
    }
}
