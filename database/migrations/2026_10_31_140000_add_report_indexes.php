<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns used by list filters, the dashboard and analytics.
     *
     * Foreign key columns already have an index on MySQL (created with the constraint),
     * so an index is only added where none covers the column yet.
     *
     * @var array<string, array<int, string>>
     */
    private array $columns = [
        'orders' => ['status', 'created_at', 'deleted_at'],
        'order_items' => ['product_id', 'filament_id'],
        'investments' => ['invested_at', 'category_id'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasIndex($table, [$column])) {
                    continue;
                }

                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($column));
            }
        }
    }

    /**
     * Drop only the indexes this migration created (default "<table>_<column>_index" names).
     */
    public function down(): void
    {
        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasIndex($table, "{$table}_{$column}_index")) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex([$column]));
                }
            }
        }
    }
};
