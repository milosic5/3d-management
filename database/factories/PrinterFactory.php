<?php

namespace Database\Factories;

use App\Models\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Printer>
 */
class PrinterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Printer '.fake()->unique()->bothify('??-##'),
            'total_working_hours' => fake()->numberBetween(0, 2000),
            'last_nozzle_change_date' => null,
            'last_nozzle_working_hours' => 0,
            'current_nozzle_diameter' => 0.4,
        ];
    }
}
