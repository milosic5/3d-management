<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('received', 'printing', 'finished', 'delivered', 'cancelled') DEFAULT 'received'");

            return;
        }

        // SQLite stores enums as a CHECK constraint, so the column has to be rebuilt.
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['received', 'printing', 'finished', 'delivered', 'cancelled'])->default('received')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('received', 'printing', 'finished', 'delivered') DEFAULT 'received'");

            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['received', 'printing', 'finished', 'delivered'])->default('received')->change();
        });
    }
};
