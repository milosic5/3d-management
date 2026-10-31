<?php

use App\Models\Filament;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
    $this->order = Order::factory()->status('delivered')->create(['created_at' => '2026-10-10 10:00:00']);
});

function soldItem(Order $order, float $revenue, array $item = [], array $product = []): void
{
    OrderItem::factory()->for($order)->create([
        'product_id' => Product::factory()->create($product)->id,
        'quantity' => 1,
        'unit_price' => $revenue,
        ...$item,
    ]);
}

it('groups material revenue by the filament used on each order item', function () {
    $pla = Filament::factory()->material('pla')->create();
    $petg = Filament::factory()->material('petg')->create();

    // Item filament wins over the product's own filament and legacy material column.
    soldItem($this->order, 100, ['filament_id' => $petg->id], ['filament_id' => $pla->id, 'material' => 'pla']);
    // No item filament: fall back to the product's filament.
    soldItem($this->order, 20, [], ['filament_id' => $pla->id, 'material' => 'petg']);
    // No filament anywhere: fall back to the legacy products.material.
    soldItem($this->order, 5, [], ['material' => 'petg']);
    // Nothing known.
    soldItem($this->order, 1);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('analytics.index', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page->where('materialBreakdown', function ($rows) {
            $revenue = collect($rows)->mapWithKeys(fn ($r) => [$r['material'] ?? 'unknown' => (float) $r['revenue']]);

            return $revenue->all() === ['petg' => 105.0, 'pla' => 20.0, 'unknown' => 1.0];
        }));
});
