<?php

use App\Models\Printer;
use App\Models\PrinterMaintenance;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
});

function maintainedIn(Printer $printer, string $month): void
{
    PrinterMaintenance::create([
        'printer_id' => $printer->id,
        'maintenance_month' => $month,
        'working_hours_at_maintenance' => 100,
        'hours_printed_this_month' => 10,
        'lubricated' => true,
    ]);
}

it('flags printers without maintenance for the current month', function () {
    $done = Printer::factory()->create();
    $missing = Printer::factory()->create();
    maintainedIn($done, '2026-10-01');
    maintainedIn($missing, '2026-09-01');

    expect($done->needs_maintenance)->toBeFalse()
        ->and($missing->needs_maintenance)->toBeTrue();
});

it('looks at next month from the 28th on', function () {
    $this->travelTo(now()->setDate(2026, 10, 28));
    $printer = Printer::factory()->create();
    maintainedIn($printer, '2026-10-01');

    expect($printer->needs_maintenance)->toBeTrue();

    maintainedIn($printer, '2026-11-01');

    expect($printer->fresh()->needs_maintenance)->toBeFalse();
});

it('uses eager-loaded maintenances instead of a query per printer', function () {
    foreach (range(1, 3) as $i) {
        maintainedIn(Printer::factory()->create(), '2026-10-01');
    }

    $printers = Printer::with('maintenances')->get();

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    $flags = $printers->map->needs_maintenance->all();

    expect($flags)->toBe([false, false, false])
        ->and($queries)->toBe(0);
});
