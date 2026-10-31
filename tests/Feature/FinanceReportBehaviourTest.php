<?php

/*
 * Characterisation tests for the period ranges, revenue-vs-costs and "Cepovi" grouping
 * shared by the dashboard and analytics. They pin the behaviour across refactors.
 */

use App\Models\Investment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
    $this->admin = User::factory()->admin()->create();
});

function numbersEqual(iterable $actual, array $expected): bool
{
    $normalize = fn (iterable $values) => json_encode(
        collect($values)->map(fn ($v) => is_numeric($v) ? (float) $v : $v)->all()
    );

    return $normalize($actual) === $normalize($expected);
}

dataset('periods', [
    'this month' => ['this_month', 2],
    'last month' => ['last_month', 1],
    'this year' => ['this_year', 4],
    'last year' => ['last_year', 1],
    'specific month' => ['2026-09', 1],
    // Unknown periods mean "all time": from 2000-01-01 up to the end of today.
    'all time' => ['all_time', 4],
]);

it('resolves the same period ranges on dashboard and analytics', function (string $period, int $expected) {
    foreach (['1999-12-31', '2025-06-10', '2026-01-10', '2026-09-10', '2026-10-10', '2026-10-20'] as $date) {
        Order::factory()->create(['created_at' => "{$date} 10:00:00"]);
    }

    $this->actingAs($this->admin)
        ->get(route('dashboard', ['period' => $period]))
        ->assertInertia(fn (Assert $page) => $page->where('stats.kpis.totalOrders', $expected)->etc());

    $this->actingAs($this->admin)
        ->get(route('analytics.index', ['period' => $period]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('monthlyOrders', fn ($rows) => collect($rows)->sum('total') === $expected)
            ->etc());
})->with('periods');

it('merges revenue and cost months on dashboard and analytics', function () {
    Order::factory()->status('delivered')->create(['total_price' => 300, 'created_at' => '2026-08-05 10:00:00']);
    Order::factory()->status('delivered')->create(['total_price' => 1000, 'created_at' => '2026-10-05 10:00:00']);
    Order::factory()->status('received')->create(['total_price' => 999, 'created_at' => '2026-10-06 10:00:00']);
    Investment::factory()->create(['amount' => 200, 'invested_at' => '2026-09-05']);
    Investment::factory()->create(['amount' => 100, 'invested_at' => '2026-10-05']);

    $expected = [
        ['month' => '2026-08', 'revenue' => 300, 'costs' => 0, 'profit' => 300],
        ['month' => '2026-09', 'revenue' => 0, 'costs' => 200, 'profit' => -200],
        ['month' => '2026-10', 'revenue' => 1000, 'costs' => 100, 'profit' => 900],
    ];
    $matches = fn ($rows) => count($rows) === 3
        && collect($rows)->zip($expected)->every(fn ($pair) => numbersEqual($pair[0], $pair[1]));

    $this->actingAs($this->admin)
        ->get(route('dashboard', ['period' => 'this_year']))
        ->assertInertia(fn (Assert $page) => $page->where('stats.revenueVsCosts', $matches)->etc());

    $this->actingAs($this->admin)
        ->get(route('analytics.index', ['period' => 'this_year']))
        ->assertInertia(fn (Assert $page) => $page->where('revenueVsCosts', $matches)->etc());
});

it('groups cap products into a single "Cepovi" row', function () {
    $order = Order::factory()->status('delivered')->create(['created_at' => '2026-10-05 10:00:00']);
    $vase = Product::factory()->create(['name' => 'Vaza', 'color_hex' => '#111111']);
    $redCap = Product::factory()->create(['name' => 'Čep crveni', 'color_hex' => '#ff0000']);
    $blueCap = Product::factory()->create(['name' => 'cep plavi', 'color_hex' => '#0000ff']);

    OrderItem::factory()->for($order)->for($vase)->create(['quantity' => 1, 'unit_price' => 1000, 'print_cost' => 100, 'print_time_minutes' => 120]);
    // Whole hours: "/ 60" is integer division on SQLite but decimal on MySQL.
    OrderItem::factory()->for($order)->for($redCap)->create(['quantity' => 2, 'unit_price' => 100, 'print_cost' => 50, 'print_time_minutes' => 60, 'weight_grams' => 100]);
    OrderItem::factory()->for($order)->for($blueCap)->create(['quantity' => 1, 'unit_price' => 300, 'print_cost' => 30, 'print_time_minutes' => 120, 'weight_grams' => 50]);

    $this->actingAs($this->admin)
        ->get(route('dashboard', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.revenueByProduct', fn ($rows) => numbersEqual(
                collect($rows)->map(fn ($r) => [$r['name'], $r['color_hex'], $r['revenue']])->flatten()->all(),
                ['Vaza', '#111111', 1000, 'Cepovi', '#f97316', 500],
            ))
            ->where('stats.topProducts.1', fn ($row) => numbersEqual($row, [
                'name' => 'Cepovi',
                'color_hex' => '#f97316',
                'units_sold' => 3,
                'revenue' => 500,
                'avg_price' => 200,
                'print_hours' => 3,
            ]))
            ->etc());

    $this->actingAs($this->admin)
        ->get(route('analytics.index', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('productProfitability', 2)
            ->where('productProfitability.1', fn ($row) => numbersEqual($row, [
                // id is kept from the first (highest-revenue) cap, not summed.
                'id' => $blueCap->id,
                'name' => 'Cepovi',
                'color_hex' => '#f97316',
                'units_sold' => 3,
                'revenue' => 500,
                'print_costs' => 80,
                'filament_cost' => 0,
                'total_grams' => 150,
                'total_minutes' => 180,
                'gross_profit' => 420,
                'margin' => 84,
            ]))
            ->etc());
});
