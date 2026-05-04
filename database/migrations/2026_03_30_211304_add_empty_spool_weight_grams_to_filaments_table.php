<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('filaments', function (Blueprint $table) {
            $table->decimal('empty_spool_weight_grams', 8, 2)->nullable()->after('price_per_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filaments', function (Blueprint $table) {
            $table->dropColumn('empty_spool_weight_grams');
        });
    }
};
