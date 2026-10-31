<?php

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

it('lets admins view and save settings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('settings.index'))
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Index'));

    $this->actingAs($admin)
        ->post(route('settings.store'), ['app_name' => 'Moja štamparija', 'default_locale' => 'sr'])
        ->assertRedirect();

    expect(Setting::get('app_name'))->toBe('Moja štamparija')
        ->and(Setting::get('default_locale'))->toBe('sr');

    $this->actingAs($admin)
        ->post(route('settings.store'), ['default_locale' => 'de'])
        ->assertSessionHasErrors('default_locale');
});

it('keeps settings admin-only', function () {
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)->get(route('settings.index'))->assertForbidden();
    $this->actingAs($operator)->post(route('settings.store'), ['app_name' => 'X'])->assertForbidden();
});

it('shares the configured app name, falling back to the default', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user)->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page->where('appName', '3D PrintShop')->etc());

    Setting::set('app_name', 'Moja štamparija');

    $this->actingAs($user)->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page->where('appName', 'Moja štamparija')->etc());
});

it('starts the frontend in the default locale unless the user picked one', function () {
    $user = User::factory()->operator()->create();
    Setting::set('default_locale', 'sr');

    $this->actingAs($user)->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'sr')->etc());

    $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en']);

    $this->actingAs($user)->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en')->etc());
});

it('prints the app name on the order pdf', function () {
    Setting::set('app_name', 'Moja štamparija');
    $order = Order::factory()->create();

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(fn ($view, $data) => $view === 'pdf.order' && $data['appName'] === 'Moja štamparija')
        ->andReturnSelf();
    Pdf::shouldReceive('setPaper')->andReturnSelf();
    Pdf::shouldReceive('download')->andReturn(response('pdf'));

    $this->actingAs(User::factory()->operator()->create())
        ->get(route('export.order.pdf', $order))
        ->assertOk();
});

it('reads settings from the cache instead of the database on every request', function () {
    Setting::set('app_name', 'Moja štamparija');
    Setting::set('default_locale', 'sr');
    $user = User::factory()->operator()->create();

    $this->actingAs($user)->get(route('profile.edit'))->assertOk();

    $settingsQueries = 0;
    DB::listen(function ($query) use (&$settingsQueries) {
        if (str_contains($query->sql, 'settings')) {
            $settingsQueries++;
        }
    });

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page->where('appName', 'Moja štamparija')->where('locale', 'sr')->etc());

    expect($settingsQueries)->toBe(0);

    Setting::set('app_name', 'Nova štamparija');

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page->where('appName', 'Nova štamparija')->etc());
});
