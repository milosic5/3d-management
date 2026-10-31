<?php

use App\Enums\FilamentMaterial;
use App\Enums\OrderStatus;
use App\Models\Filament;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

it('casts order status to the enum and rejects unknown statuses', function () {
    $order = Order::factory()->status('printing')->create();
    $operator = User::factory()->operator()->create();

    expect($order->fresh()->status)->toBe(OrderStatus::Printing);

    $this->actingAs($operator)
        ->from(route('orders.index'))
        ->put(route('orders.update', $order), ['status' => 'shipped'])
        ->assertSessionHasErrors('status');

    $this->actingAs($operator)
        ->put(route('orders.update', $order), ['status' => 'finished'])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Finished);
});

it('validates filament materials against the enum', function () {
    $payload = [
        'brand' => 'Prusament',
        'name' => 'Galaxy Black',
        'color_name' => 'Black',
        'color_hex' => '#000000',
        'price_per_kg' => 2500,
    ];
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)
        ->post(route('filaments.store'), [...$payload, 'material' => 'abs'])
        ->assertSessionHasErrors('material');

    $this->actingAs($operator)
        ->post(route('filaments.store'), [...$payload, 'material' => 'petg'])
        ->assertSessionHasNoErrors();

    expect(Filament::firstWhere('name', 'Galaxy Black')->material)->toBe(FilamentMaterial::Petg);
});

it('stores materials as plain strings so new enum cases need no migration', function () {
    $filament = Filament::factory()->create();

    DB::table('filaments')->where('id', $filament->id)->update(['material' => 'tpu']);

    expect(DB::table('filaments')->where('id', $filament->id)->value('material'))->toBe('tpu');
});

it('shares status and material options with the frontend', function () {
    $this->actingAs(User::factory()->operator()->create())
        ->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('enums.orderStatuses', OrderStatus::options())
            ->where('enums.materials', FilamentMaterial::options())
            ->etc());

    expect(collect(OrderStatus::options())->pluck('value')->all())
        ->toBe(['received', 'printing', 'finished', 'delivered', 'cancelled']);
});
