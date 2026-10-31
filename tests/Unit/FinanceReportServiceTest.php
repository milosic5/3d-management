<?php

use App\Services\FinanceReportService;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-10-15 12:00:00');
    $this->finance = new FinanceReportService;
});

afterEach(fn () => Carbon::setTestNow());

it('resolves period filters into date ranges', function (string $period, string $start, string $end) {
    [$from, $to] = $this->finance->dateRange($period);

    expect($from->toDateTimeString())->toBe($start)
        ->and($to->toDateTimeString())->toBe($end);
})->with([
    'this month' => ['this_month', '2026-10-01 00:00:00', '2026-10-31 23:59:59'],
    'last month' => ['last_month', '2026-09-01 00:00:00', '2026-09-30 23:59:59'],
    'this year' => ['this_year', '2026-01-01 00:00:00', '2026-12-31 23:59:59'],
    'last year' => ['last_year', '2025-01-01 00:00:00', '2025-12-31 23:59:59'],
    'specific month' => ['2026-02', '2026-02-01 00:00:00', '2026-02-28 23:59:59'],
    'all time' => ['all_time', '2000-01-01 00:00:00', '2026-10-15 23:59:59'],
]);

it('merges cap rows into one "Cepovi" row and keeps the others', function () {
    $rows = [
        (object) ['id' => 1, 'name' => 'Vaza', 'color_hex' => '#111111', 'revenue' => '1000', 'avg_price' => '1000'],
        (object) ['id' => 3, 'name' => 'cep plavi', 'color_hex' => '#0000ff', 'revenue' => '300', 'avg_price' => '300'],
        (object) ['id' => 2, 'name' => 'Čep crveni', 'color_hex' => '#ff0000', 'revenue' => '200', 'avg_price' => '100'],
    ];

    $grouped = $this->finance->groupCepovi($rows)->all();

    expect($grouped)->toHaveCount(2)
        ->and($grouped[0])->toBe(['id' => 1, 'name' => 'Vaza', 'color_hex' => '#111111', 'revenue' => '1000', 'avg_price' => '1000'])
        ->and($grouped[1])->toEqual(['id' => 3, 'name' => 'Cepovi', 'color_hex' => '#f97316', 'revenue' => 500.0, 'avg_price' => 200.0]);
});
