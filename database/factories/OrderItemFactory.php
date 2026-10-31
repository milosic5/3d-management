<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * print_cost, weight_grams and print_time_minutes are totals for the line, not per unit.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'filament_id' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 200, 5000),
            'print_cost' => fake()->randomFloat(2, 20, 500),
            'print_time_minutes' => fake()->numberBetween(10, 1200),
            'weight_grams' => fake()->randomFloat(2, 5, 1000),
            'color_name' => null,
            'color_hex' => null,
            'notes' => null,
        ];
    }
}
