<?php

namespace App\Http\Controllers;

use App\Models\Filament;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FilamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Filament::query();
        
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
        }
        if ($material = $request->input('material')) {
            $query->where('material', $material);
        }

        return Inertia::render('Filaments/Index', [
            'filaments' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'material'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Filaments/Create', [
            'brands' => Filament::distinct()->pluck('brand'),
            'names' => Filament::distinct()->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Filament::class);

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:filaments,name',
            'material' => 'required|in:pla,petg',
            'color_name' => 'required|string|max:255',
            'color_hex' => 'required|string|size:7',
            'price_per_kg' => 'required|numeric|min:0',
            'empty_spool_weight_grams' => 'nullable|numeric|min:0|max:9999',
            'notes' => 'nullable|string'
        ]);

        Filament::create($validated);
        return redirect()->route('filaments.index');
    }

    public function edit(Filament $filament)
    {
        return Inertia::render('Filaments/Edit', [
            'filament' => $filament,
            'brands' => Filament::distinct()->pluck('brand'),
            'names' => Filament::distinct()->pluck('name'),
        ]);
    }

    public function update(Request $request, Filament $filament)
    {
        $this->authorize('update', $filament);

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:filaments,name,' . $filament->id,
            'material' => 'required|in:pla,petg',
            'color_name' => 'required|string|max:255',
            'color_hex' => 'required|string|size:7',
            'price_per_kg' => 'required|numeric|min:0',
            'empty_spool_weight_grams' => 'nullable|numeric|min:0|max:9999',
            'notes' => 'nullable|string'
        ]);

        $filament->update($validated);
        return redirect()->route('filaments.index');
    }

    public function destroy(Filament $filament)
    {
        $this->authorize('delete', $filament);
        
        if ($filament->products()->exists()) {
            return back()->with('error', 'Cannot delete filament. It is linked to ' . $filament->products()->count() . ' products.');
        }

        $filament->delete();
        return redirect()->route('filaments.index');
    }
}
