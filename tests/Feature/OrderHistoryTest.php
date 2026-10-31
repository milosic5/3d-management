<?php

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->operator()->create();
    $this->product = Product::factory()->create();
});

function fullOrderPayload(Order $order, Product $product, string $status): array
{
    return [
        'customer_name' => $order->customer_name,
        'notes' => null,
        'status' => $status,
        'estimated_print_minutes' => null,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 500,
        ]],
    ];
}

it('logs the real previous status when a full order update changes it', function () {
    $order = Order::factory()->status('received')->create();

    $this->actingAs($this->user)
        ->put(route('orders.update', $order), fullOrderPayload($order, $this->product, 'printing'))
        ->assertRedirect(route('orders.index'));

    $history = OrderHistory::where('order_id', $order->id)->orderBy('id')->get();

    expect($history->pluck('action')->all())->toBe(['updated', 'status_changed'])
        ->and($history[0]->from_status)->toBe('received')
        ->and($history[1]->from_status)->toBe('received')
        ->and($history[1]->to_status)->toBe('printing');
});

it('does not log a status change when a full order update keeps the status', function () {
    $order = Order::factory()->status('printing')->create();

    $this->actingAs($this->user)
        ->put(route('orders.update', $order), fullOrderPayload($order, $this->product, 'printing'))
        ->assertRedirect(route('orders.index'));

    $history = OrderHistory::where('order_id', $order->id)->get();

    expect($history->pluck('action')->all())->toBe(['updated'])
        ->and($history[0]->from_status)->toBe('printing')
        ->and($history[0]->to_status)->toBe('printing');
});
