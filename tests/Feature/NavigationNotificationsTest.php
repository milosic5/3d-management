<?php

use App\Models\Filament;
use App\Models\Order;
use App\Models\Printer;
use App\Models\PrinterMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
    $this->user = User::factory()->operator()->create();
});

function notificationsFor(User $user): array
{
    $notifications = null;

    test()->actingAs($user)->get(route('profile.edit'))
        ->assertInertia(function (Assert $page) use (&$notifications) {
            $notifications = $page->toArray()['props']['notifications'];
        });

    return $notifications;
}

it('caches the navigation counters between requests', function () {
    Order::factory()->status('received')->create();
    Filament::factory()->create(['stock_rolls' => 0]);
    Printer::factory()->create();

    expect(notificationsFor($this->user))->toBe([
        'activeOrders' => 1,
        'emptyFilaments' => 1,
        'printerWarnings' => 1,
        'total' => 3,
    ]);

    $tables = [];
    DB::listen(function ($query) use (&$tables) {
        preg_match('/from\s+["`]?(\w+)/i', $query->sql, $match);
        $tables[] = $match[1] ?? null;
    });

    notificationsFor($this->user);

    expect(array_intersect($tables, ['orders', 'filaments', 'printers', 'printer_maintenances']))->toBe([]);
});

it('refreshes the counters as soon as orders, filaments or printers change', function () {
    $filament = Filament::factory()->create(['stock_rolls' => 2]);
    $printer = Printer::factory()->create(['total_working_hours' => 100, 'last_nozzle_working_hours' => 0]);

    expect(notificationsFor($this->user))->toMatchArray(['activeOrders' => 0, 'emptyFilaments' => 0, 'printerWarnings' => 1]);

    $order = Order::factory()->status('printing')->create();
    expect(notificationsFor($this->user)['activeOrders'])->toBe(1);

    $order->update(['status' => 'delivered']);
    expect(notificationsFor($this->user)['activeOrders'])->toBe(0);

    $order->update(['status' => 'received']);
    $order->delete();
    expect(notificationsFor($this->user)['activeOrders'])->toBe(0);

    $order->restore();
    expect(notificationsFor($this->user)['activeOrders'])->toBe(1);

    $filament->update(['stock_rolls' => 0]);
    expect(notificationsFor($this->user)['emptyFilaments'])->toBe(1);

    PrinterMaintenance::create([
        'printer_id' => $printer->id,
        'maintenance_month' => '2026-10-01',
        'working_hours_at_maintenance' => 100,
        'hours_printed_this_month' => 10,
        'lubricated' => true,
    ]);
    expect(notificationsFor($this->user)['printerWarnings'])->toBe(0);

    $printer->update(['total_working_hours' => 600, 'last_nozzle_working_hours' => 0]);
    expect(notificationsFor($this->user)['printerWarnings'])->toBe(1);
});
