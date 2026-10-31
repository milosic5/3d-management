<?php

use Illuminate\Support\Facades\Schema;

it('indexes the columns used by filters and reports', function (string $table, string $column) {
    expect(Schema::hasIndex($table, [$column]))->toBeTrue();
})->with([
    ['orders', 'status'],
    ['orders', 'created_at'],
    ['orders', 'deleted_at'],
    ['order_items', 'product_id'],
    ['order_items', 'filament_id'],
    ['investments', 'invested_at'],
    ['investments', 'category_id'],
]);

it('drops only its own indexes on rollback', function () {
    $migration = require database_path('migrations/2026_10_31_140000_add_report_indexes.php');

    $migration->down();

    expect(Schema::hasIndex('orders', ['status']))->toBeFalse()
        ->and(Schema::hasIndex('orders', ['order_number']))->toBeTrue();

    $migration->up();

    expect(Schema::hasIndex('orders', ['status']))->toBeTrue();
});
