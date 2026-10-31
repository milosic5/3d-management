<?php

use App\Exports\OrdersExport;
use App\Models\Investment;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
    $this->admin = User::factory()->admin()->create();
    $this->operator = User::factory()->operator()->create();
});

function deliveredOrder(float $total, string $createdAt = '2026-10-10 10:00:00'): Order
{
    $order = Order::factory()->status('delivered')->create(['total_price' => $total, 'created_at' => $createdAt]);
    OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => $total]);

    return $order;
}

it('soft deletes an order and keeps its items and history', function () {
    $order = deliveredOrder(1000);
    OrderHistory::create(['order_id' => $order->id, 'user_id' => $this->operator->id, 'action' => 'created', 'to_status' => 'delivered']);

    $this->actingAs($this->operator)->delete(route('orders.destroy', $order))->assertRedirect(route('orders.index'));

    expect(Order::find($order->id))->toBeNull()
        ->and(Order::withTrashed()->find($order->id)->trashed())->toBeTrue()
        ->and(OrderItem::where('order_id', $order->id)->count())->toBe(1)
        ->and(OrderHistory::where('order_id', $order->id)->count())->toBe(1);
});

it('does not reuse the order number of a deleted order', function () {
    $first = Order::factory()->create(['created_at' => '2026-10-01 10:00:00']);
    $second = Order::factory()->create(['created_at' => '2026-10-02 10:00:00']);
    $second->delete();

    $third = Order::factory()->create(['created_at' => '2026-10-03 10:00:00']);

    expect($first->order_number)->toBe('ORD-202610-0001')
        ->and($third->order_number)->toBe('ORD-202610-0003');
});

it('excludes deleted orders from the dashboard', function () {
    deliveredOrder(1000);
    deliveredOrder(5000)->delete();
    Order::factory()->status('received')->create()->delete();

    $this->actingAs($this->admin)
        ->get(route('dashboard', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.kpis.totalRevenue', fn ($v) => (float) $v === 1000.0)
            ->where('stats.kpis.totalOrders', 1)
            ->where('stats.kpis.activeOrders', 0)
            ->where('stats.revenueVsCosts.0.revenue', 1000)
            ->where('stats.revenueByProduct.0.revenue', fn ($v) => (float) $v === 1000.0)
            ->where('stats.profitabilityTimeline.0.orders', 1)
            ->where('stats.operationalStats.totalPrints', 1)
            ->where('stats.ordersByStatus', ['delivered' => 1])
            ->has('stats.recentOrders', 1)
            ->has('stats.topProducts', 1)
            ->etc());
});

it('excludes deleted orders from analytics', function () {
    deliveredOrder(1000);
    deliveredOrder(5000)->delete();
    Investment::factory()->create(['amount' => 100, 'invested_at' => '2026-10-05']);

    $this->actingAs($this->admin)
        ->get(route('analytics.index', ['period' => 'this_month']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('revenueVsCosts.0.revenue', 1000)
            ->where('monthlyOrders.0.total', 1)
            ->has('topCustomers', 1)
            ->where('topCustomers.0.total_orders', 1)
            ->has('productProfitability', 1)
            ->where('productProfitability.0.revenue', fn ($v) => (float) $v === 1000.0)
            ->where('materialBreakdown', fn ($rows) => collect($rows)->sum(fn ($r) => (float) $r['revenue']) === 1000.0)
            ->etc());
});

it('excludes deleted orders from the excel export', function () {
    Excel::fake();
    $kept = deliveredOrder(1000);
    deliveredOrder(5000)->delete();

    $this->actingAs($this->operator)->get(route('export.orders.excel'));

    Excel::assertDownloaded('orders_2026-10-15.xlsx', function (OrdersExport $export) use ($kept) {
        return $export->collection()->pluck('id')->all() === [$kept->id];
    });
});

it('lets only admins see, restore and permanently delete trashed orders', function () {
    $order = deliveredOrder(1000);
    $order->delete();

    $this->actingAs($this->operator)->get(route('orders.trash'))->assertForbidden();
    $this->actingAs($this->operator)->post(route('orders.restore', $order->id))->assertForbidden();
    $this->actingAs($this->operator)->delete(route('orders.forceDelete', $order->id))->assertForbidden();

    $this->actingAs($this->admin)->get(route('orders.trash'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Orders/Trash')
            ->where('orders.data.0.id', $order->id));

    $this->actingAs($this->admin)->post(route('orders.restore', $order->id))->assertRedirect();
    expect($order->fresh()->trashed())->toBeFalse();

    $order->delete();
    $this->actingAs($this->admin)->delete(route('orders.forceDelete', $order->id))->assertRedirect();
    expect(Order::withTrashed()->find($order->id))->toBeNull()
        ->and(OrderItem::where('order_id', $order->id)->count())->toBe(0);
});

it('does not delete a user whose orders are only in the trash', function () {
    $creator = User::factory()->operator()->create();
    $order = Order::factory()->create(['created_by' => $creator->id]);
    $order->delete();

    $this->actingAs($this->admin)->delete(route('users.destroy', $creator))->assertRedirect();

    expect(User::find($creator->id))->not->toBeNull()
        ->and(Order::withTrashed()->find($order->id))->not->toBeNull();
});
