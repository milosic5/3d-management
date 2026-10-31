<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allowed materials are enforced by App\Enums\FilamentMaterial, so new ones need no migration.
     */
    public function up(): void
    {
        Schema::table('filaments', function (Blueprint $table) {
            $table->string('material', 20)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('material', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations (only valid while every stored material is pla or petg).
     */
    public function down(): void
    {
        Schema::table('filaments', function (Blueprint $table) {
            $table->enum('material', ['pla', 'petg'])->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->enum('material', ['pla', 'petg'])->nullable()->change();
        });
    }
};
