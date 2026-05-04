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
        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filament_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('temperature')->nullable();
            $table->decimal('flow_ratio', 8, 4)->nullable();
            $table->decimal('pressure_advance', 8, 4)->nullable();
            $table->decimal('max_volumetric_speed', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibrations');
    }
};
