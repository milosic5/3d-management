<?php

use App\Models\Order;
use App\Models\User;

it('seeds a working demo database', function () {
    $this->seed();

    expect(User::where('role', 'admin')->exists())->toBeTrue()
        ->and(User::where('role', 'operator')->exists())->toBeTrue()
        ->and(Order::count())->toBeGreaterThan(0);
});
