<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * order_number is left empty so Order::booted() generates it.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'status' => 'received',
            'notes' => null,
            'total_price' => fake()->randomFloat(2, 500, 20000),
            'estimated_print_minutes' => fake()->numberBetween(30, 1200),
            'created_by' => User::factory(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
