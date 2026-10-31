<?php

use App\Models\Calibration;
use App\Models\Filament;
use App\Models\Order;
use App\Models\Packaging;
use App\Models\Printer;
use App\Models\PrinterMaintenance;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

function makeDeletable(string $type): Model
{
    return match ($type) {
        'printer' => Printer::factory()->create(),
        'packaging' => Packaging::factory()->create(),
        'filament' => Filament::factory()->create(),
        'product' => Product::factory()->create(),
        'calibration' => Calibration::create([
            'filament_id' => Filament::factory()->create()->id,
            'temperature' => 215,
            'flow_ratio' => 0.98,
            'pressure_advance' => 0.04,
            'max_volumetric_speed' => 15,
        ]),
        'maintenance' => PrinterMaintenance::create([
            'printer_id' => Printer::factory()->create()->id,
            'maintenance_month' => '2026-10-01',
            'working_hours_at_maintenance' => 100,
            'hours_printed_this_month' => 10,
            'lubricated' => true,
        ]),
    };
}

function stillExists(Model $model): bool
{
    return $model::query()->whereKey($model->getKey())->exists();
}

dataset('admin-only deletes', [
    'printer' => ['printer', 'printers.destroy'],
    'packaging' => ['packaging', 'packagings.destroy'],
    'filament' => ['filament', 'filaments.destroy'],
    'product' => ['product', 'products.destroy'],
    'calibration' => ['calibration', 'calibrations.destroy'],
    'printer maintenance' => ['maintenance', 'maintenances.destroy'],
]);

it('forbids operators from deleting', function (string $type, string $route) {
    $model = makeDeletable($type);

    $this->actingAs(User::factory()->operator()->create())
        ->delete(route($route, $model))
        ->assertForbidden();

    expect(stillExists($model))->toBeTrue();
})->with('admin-only deletes');

it('lets admins delete', function (string $type, string $route) {
    $model = makeDeletable($type);

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route($route, $model))
        ->assertRedirect();

    expect(stillExists($model))->toBeFalse();
})->with('admin-only deletes');

it('lets operators and admins delete orders', function (string $role) {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->{$role}()->create())
        ->delete(route('orders.destroy', $order))
        ->assertRedirect(route('orders.index'));

    expect(stillExists($order))->toBeFalse();
})->with(['operator', 'admin']);

it('keeps the product trash admin-only', function () {
    $product = Product::factory()->create();
    $product->delete();
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)->get(route('products.trash'))->assertForbidden();
    $this->actingAs($operator)->post(route('products.restore', $product->id))->assertForbidden();
    $this->actingAs($operator)->delete(route('products.forceDelete', $product->id))->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())->get(route('products.trash'))->assertOk();
});

it('lets operators manage printers, maintenance and packaging stock', function () {
    $operator = User::factory()->operator()->create();
    $printer = Printer::factory()->create(['total_working_hours' => 100]);
    $packaging = Packaging::factory()->create(['stock' => 3]);
    $filament = Filament::factory()->create();

    $this->actingAs($operator)->post(route('printers.store'), ['name' => 'P1S', 'total_working_hours' => 0])->assertRedirect();
    $this->actingAs($operator)->put(route('printers.update', $printer), ['name' => 'X1C', 'total_working_hours' => 120])->assertRedirect();
    $this->actingAs($operator)->post(route('printers.reset-nozzle', $printer), ['working_hours_at_change' => 120, 'nozzle_diameter' => 0.4])->assertRedirect();
    $this->actingAs($operator)->post(route('printers.maintenance', $printer), ['working_hours_at_maintenance' => 130, 'lubricated' => true])->assertRedirect();
    $maintenance = $printer->maintenances()->first();
    $this->actingAs($operator)->put(route('maintenances.update', $maintenance), [
        'maintenance_month' => '2026-10', 'working_hours_at_maintenance' => 130, 'hours_printed_this_month' => 10, 'lubricated' => false,
    ])->assertRedirect();

    $this->actingAs($operator)->post(route('packagings.store'), ['type' => 'envelope', 'length' => 20, 'width' => 15, 'stock' => 5])->assertRedirect();
    $this->actingAs($operator)->put(route('packagings.update', $packaging), ['length' => 20, 'width' => 15, 'height' => 10, 'stock' => 4])->assertRedirect();
    $this->actingAs($operator)->post(route('packagings.add-stock', $packaging))->assertRedirect();
    $this->actingAs($operator)->post(route('packagings.remove-stock', $packaging))->assertRedirect();

    $this->actingAs($operator)->post(route('filaments.update-stock', $filament), ['stock_rolls' => 2])->assertRedirect();

    expect(Printer::count())->toBe(2)
        ->and($printer->fresh()->name)->toBe('X1C')
        ->and($maintenance->fresh()->lubricated)->toBeFalsy()
        ->and($packaging->fresh()->stock)->toBe(4)
        ->and($filament->fresh()->stock_rolls)->toBe(2);
});

it('lets operators download an order pdf', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->operator()->create())
        ->get(route('export.order.pdf', $order))
        ->assertOk()
        ->assertDownload("order_{$order->order_number}.pdf");
});
