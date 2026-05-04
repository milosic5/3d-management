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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('material', ['pla', 'petg']);
            $table->foreignId('filament_id')->nullable()->constrained('filaments')->nullOnDelete();
            $table->string('color_name')->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->decimal('weight_grams', 8, 2);
            $table->integer('print_time_minutes');
            $table->decimal('price', 10, 2);
            $table->string('image_path')->nullable();
            $table->string('model_file_path')->nullable();
            $table->string('model_file_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
