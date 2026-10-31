<?php

use App\Models\Filament;
use App\Models\Investment;
use App\Models\InvestmentCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = InvestmentCategory::factory()->create(['name' => 'Filament']);
});

function investmentPayload(array $overrides = []): array
{
    return [
        'category_id' => test()->category->id,
        'name' => 'Rolna',
        'unit_cost' => 2800,
        'quantity' => 1,
        'unit' => 'kom',
        'invested_at' => '2026-10-10',
        ...$overrides,
    ];
}

it('updates the price of the selected filament even when names differ', function () {
    $filament = Filament::factory()->create(['name' => 'Galaxy Black (renamed)', 'price_per_kg' => 2000]);

    $this->actingAs($this->admin)
        ->post(route('investments.store'), investmentPayload(['filament_id' => $filament->id, 'name' => 'Galaxy Black']))
        ->assertRedirect(route('investments.index'));

    expect(Investment::first()->filament_id)->toBe($filament->id)
        ->and((float) $filament->fresh()->price_per_kg)->toBe(2800.0);
});

it('does not touch a filament that only shares the investment name', function () {
    $filament = Filament::factory()->create(['name' => 'Galaxy Black', 'price_per_kg' => 2000]);

    $this->actingAs($this->admin)
        ->post(route('investments.store'), investmentPayload(['name' => 'Galaxy Black']))
        ->assertRedirect();

    expect((float) $filament->fresh()->price_per_kg)->toBe(2000.0);
});

it('recalculates both filaments when an investment moves to another filament', function () {
    $old = Filament::factory()->create(['price_per_kg' => 1000]);
    $new = Filament::factory()->create(['price_per_kg' => 1000]);
    Investment::factory()->create(['filament_id' => $old->id, 'unit_cost' => 1500, 'invested_at' => '2026-01-01']);
    $investment = Investment::factory()->create(['filament_id' => $old->id, 'unit_cost' => 3000, 'invested_at' => '2026-10-01']);

    expect((float) $old->fresh()->price_per_kg)->toBe(3000.0);

    $this->actingAs($this->admin)
        ->put(route('investments.update', $investment), investmentPayload([
            'filament_id' => $new->id,
            'category_id' => $investment->category_id,
            'unit_cost' => 3000,
            'invested_at' => '2026-10-01',
        ]))
        ->assertRedirect();

    expect((float) $new->fresh()->price_per_kg)->toBe(3000.0)
        ->and((float) $old->fresh()->price_per_kg)->toBe(1500.0);
});

it('backfills filament_id on existing investments by filament name', function () {
    $filament = Filament::factory()->create(['name' => 'Galaxy Black']);
    $matching = Investment::factory()->create(['name' => 'Galaxy Black']);
    $other = Investment::factory()->create(['name' => 'Printer service']);
    DB::table('investments')->update(['filament_id' => null]);

    $migration = require database_path('migrations/2026_10_31_130100_add_filament_id_to_investments_table.php');
    $migration->backfill();

    expect($matching->fresh()->filament_id)->toBe($filament->id)
        ->and($other->fresh()->filament_id)->toBeNull();
});
