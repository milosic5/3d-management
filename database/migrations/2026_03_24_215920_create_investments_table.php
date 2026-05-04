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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('investment_categories')->onDelete('cascade');
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->decimal('quantity', 10, 3);
            $table->string('unit');
            $table->decimal('unit_cost', 10, 4);
            $table->date('invested_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
