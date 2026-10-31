<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shares operator permissions: only orders can be deleted', function () {
    $this->actingAs(User::factory()->operator()->create())
        ->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.can', [
            'deleteOrders' => true,
            'manageOrderTrash' => false,
            'deleteProducts' => false,
            'manageProductTrash' => false,
            'deleteFilaments' => false,
            'deletePrinters' => false,
            'deleteMaintenances' => false,
            'deletePackagings' => false,
            'deleteCalibrations' => false,
        ]));
});

it('shares admin permissions: everything allowed', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page->where(
            'auth.can',
            fn ($can) => collect($can)->every(fn ($allowed) => $allowed === true) && count($can) === 9,
        ));
});
