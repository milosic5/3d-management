<?php

use App\Models\Investment;
use App\Models\Order;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

    Order::factory()->status('delivered')->create(['total_price' => 1000, 'created_at' => '2026-10-10 10:00:00']);
    Order::factory()->status('delivered')->create(['total_price' => 500, 'created_at' => '2026-10-12 10:00:00']);
    Order::factory()->status('received')->create(['total_price' => 70, 'created_at' => '2026-10-05 10:00:00']);
    Order::factory()->status('printing')->create(['total_price' => 80, 'created_at' => '2026-09-20 10:00:00']);
    Order::factory()->status('cancelled')->create(['total_price' => 90, 'created_at' => '2026-10-03 10:00:00']);
    Order::factory()->status('delivered')->create(['total_price' => 9999, 'created_at' => '2026-10-10 10:00:00'])->delete();

    Investment::factory()->create(['amount' => 300, 'invested_at' => '2026-10-02']);
    Investment::factory()->create(['amount' => 200, 'invested_at' => '2026-09-30']);
});

it('computes the dashboard KPIs', function () {
    $kpis = app(DashboardService::class)->getKpis('this_month');

    expect((float) $kpis['totalRevenue'])->toBe(1500.0)
        ->and((float) $kpis['totalCosts'])->toBe(300.0)
        ->and((float) $kpis['grossProfit'])->toBe(1200.0)
        ->and($kpis['profitMargin'])->toEqual(80.0)
        // Active orders are not limited to the period.
        ->and($kpis['activeOrders'])->toBe(2)
        ->and($kpis['avgOrderValue'])->toEqual(750.0)
        ->and($kpis['totalOrders'])->toBe(4);
});

it('returns zeros for an empty period', function () {
    $kpis = app(DashboardService::class)->getKpis('2025-01');

    expect((float) $kpis['totalRevenue'])->toBe(0.0)
        ->and((float) $kpis['totalCosts'])->toBe(0.0)
        ->and($kpis['profitMargin'])->toEqual(0)
        ->and($kpis['avgOrderValue'])->toEqual(0)
        ->and($kpis['totalOrders'])->toBe(0)
        ->and($kpis['activeOrders'])->toBe(2);
});

it('needs only one query for orders and one for investments', function () {
    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    app(DashboardService::class)->getKpis('this_month');

    expect($queries)->toBe(2);
});

it('serves repeated dashboard visits from the cache but shows changes immediately', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('dashboard', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page->where('stats.kpis.totalOrders', 4)->etc());

    $orderQueries = 0;
    DB::listen(function ($query) use (&$orderQueries) {
        if (preg_match('/from\s+["`]?(orders|order_items|investments)\b/i', $query->sql)) {
            $orderQueries++;
        }
    });

    $this->actingAs($admin)->get(route('dashboard', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page->where('stats.kpis.totalOrders', 4)->etc());

    // Only the sidebar counters may touch these tables, and they are cached too.
    expect($orderQueries)->toBe(0);

    Order::factory()->status('received')->create(['created_at' => '2026-10-14 10:00:00']);

    $this->actingAs($admin)->get(route('dashboard', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.kpis.totalOrders', 5)
            ->where('stats.kpis.activeOrders', 3)
            ->etc());
});
