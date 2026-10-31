<?php

use App\Models\Filament;
use App\Models\Investment;
use App\Models\InvestmentCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('keeps the material filter when searching filaments', function () {
    Filament::factory()->material('pla')->create(['brand' => 'Prusament', 'name' => 'Galaxy Black']);
    Filament::factory()->material('petg')->create(['brand' => 'Prusament', 'name' => 'Jet Black']);

    $this->actingAs(User::factory()->operator()->create())
        ->get(route('filaments.index', ['search' => 'Black', 'material' => 'petg']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('filaments.data', 1)
            ->where('filaments.data.0.name', 'Jet Black'));
});

it('keeps category and date filters when searching investments', function () {
    $filaments = InvestmentCategory::factory()->create();
    $tools = InvestmentCategory::factory()->create();

    Investment::factory()->create([
        'category_id' => $filaments->id, 'name' => 'Rolna PLA', 'amount' => 100, 'invested_at' => '2026-10-10',
    ]);
    // Matches the search via notes, but not the category filter.
    Investment::factory()->create([
        'category_id' => $tools->id, 'name' => 'Spatula', 'notes' => 'za PLA', 'amount' => 30, 'invested_at' => '2026-10-10',
    ]);
    // Matches search and category, but not the date filter.
    Investment::factory()->create([
        'category_id' => $filaments->id, 'name' => 'Rolna PLA stara', 'amount' => 70, 'invested_at' => '2026-01-10',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('investments.index', [
            'search' => 'PLA',
            'category' => $filaments->id,
            'from' => '2026-10-01',
            'to' => '2026-10-31',
        ]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('investments.data', 1)
            ->where('investments.data.0.name', 'Rolna PLA')
            ->where('summary.totalInvested', fn ($total) => (float) $total === 100.0));
});
