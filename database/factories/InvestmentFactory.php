<?php

namespace Database\Factories;

use App\Models\Investment;
use App\Models\InvestmentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Investment>
 */
class InvestmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The random name keeps Investment::booted() from matching a filament by accident.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => InvestmentCategory::factory(),
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'quantity' => fake()->numberBetween(1, 10),
            'unit' => 'kom',
            'unit_cost' => fn (array $attributes) => round($attributes['amount'] / $attributes['quantity'], 4),
            'invested_at' => fake()->dateTimeBetween('-1 year'),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
