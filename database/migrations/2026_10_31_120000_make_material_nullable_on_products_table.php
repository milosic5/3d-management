<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The product form no longer sends a material (it comes from the filament).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('material', ['pla', 'petg'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')->whereNull('material')->update(['material' => 'pla']);

        Schema::table('products', function (Blueprint $table) {
            $table->enum('material', ['pla', 'petg'])->nullable(false)->change();
        });
    }
};
