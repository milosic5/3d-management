<?php

use App\Models\Investment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

    $this->admin = User::factory()->admin()->create();

    $order = Order::factory()->status('delivered')->create([
        'total_price' => 1500,
        'created_at' => '2026-10-10 10:00:00',
    ]);
    OrderItem::factory()->for($order)->create();

    Order::factory()->status('cancelled')->create(['created_at' => '2026-10-11 10:00:00']);

    Investment::factory()->create([
        'amount' => 400,
        'invested_at' => '2026-10-05',
    ]);
});

it('groups dashboard revenue and costs by month', function () {
    $this->actingAs($this->admin)
        ->get(route('dashboard', ['period' => 'this_month']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('stats.revenueVsCosts.0.month', '2026-10')
            ->where('stats.revenueVsCosts.0.revenue', 1500)
            ->where('stats.revenueVsCosts.0.costs', 400)
            ->where('stats.profitabilityTimeline.0.month', '2026-10')
            ->etc());
});

it('groups analytics revenue, costs and order counts by month', function () {
    $this->actingAs($this->admin)
        ->get(route('analytics.index', ['period' => 'this_month']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Analytics/Index')
            ->where('revenueVsCosts.0.month', '2026-10')
            ->where('revenueVsCosts.0.revenue', 1500)
            ->where('revenueVsCosts.0.costs', 400)
            ->where('monthlyOrders.0.month', '2026-10')
            ->where('monthlyOrders.0.total', 2)
            ->where('monthlyOrders.0.cancelled', 1)
            ->etc());
});
