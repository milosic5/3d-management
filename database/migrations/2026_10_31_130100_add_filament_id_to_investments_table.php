<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link filament purchases to the filament itself instead of matching by name.
     */
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->foreignId('filament_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        $this->backfill();
    }

    /**
     * Existing purchases were matched to filaments by name (filaments.name is unique).
     */
    public function backfill(): void
    {
        DB::table('investments')
            ->whereNull('filament_id')
            ->update([
                'filament_id' => DB::raw('(SELECT filaments.id FROM filaments WHERE filaments.name = investments.name LIMIT 1)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('filament_id');
        });
    }
};
