<?php

use App\Models\Printer;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists printers with nozzle hours and maintenance status', function () {
    $printer = Printer::factory()->create(['total_working_hours' => 700, 'last_nozzle_working_hours' => 100]);

    $this->actingAs(User::factory()->operator()->create())
        ->get(route('printers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Printers/Index')
            ->where('printers.0.current_nozzle_hours', 600)
            ->where('printers.0.needs_maintenance', true));

    expect($printer->toArray())->not->toHaveKeys(['current_nozzle_hours', 'needs_maintenance']);
});
