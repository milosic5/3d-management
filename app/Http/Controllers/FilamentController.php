<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFilamentRequest;
use App\Http\Requests\UpdateFilamentRequest;
use App\Http\Requests\UpdateFilamentStockRequest;
use App\Models\Filament;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FilamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Filament::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }
        if ($material = $request->input('material')) {
            $query->where('material', $material);
        }

        return Inertia::render('Filaments/Index', [
            'filaments' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'material']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Filaments/Create', [
            'brands' => Filament::distinct()->pluck('brand'),
            'names' => Filament::distinct()->pluck('name'),
        ]);
    }

    public function store(StoreFilamentRequest $request)
    {
        Filament::create($request->validated());

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

    public function update(UpdateFilamentRequest $request, Filament $filament)
    {
        $filament->update($request->validated());

        return redirect()->route('filaments.index');
    }

    public function destroy(Filament $filament)
    {
        $this->authorize('delete', $filament);

        if ($filament->products()->exists()) {
            return back()->with('error', 'Cannot delete filament. It is linked to '.$filament->products()->count().' products.');
        }

        $filament->delete();

        return redirect()->route('filaments.index');
    }

    public function stock()
    {
        return Inertia::render('Filaments/Stock', [
            'filaments' => Filament::orderBy('brand')->orderBy('name')->get(),
        ]);
    }

    public function updateStock(UpdateFilamentStockRequest $request, Filament $filament)
    {
        $filament->update($request->validated());

        return back()->with('success', 'Stock updated successfully.');
    }
}
