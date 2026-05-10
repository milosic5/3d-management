<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Packaging;
use Inertia\Inertia;

class PackagingController extends Controller
{
    public function index()
    {
        $packagings = Packaging::latest()->get();
        return Inertia::render('Packaging/Index', [
            'packagings' => $packagings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:box,envelope',
            'name' => 'nullable|string|max:255',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validated['type'] === 'envelope') {
            $validated['height'] = null;
        }

        Packaging::create($validated);

        return back()->with('success', __('Packaging created successfully.'));
    }

    public function update(Request $request, Packaging $packaging)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        if ($packaging->type === 'envelope') {
            $validated['height'] = null;
        }

        $packaging->update($validated);

        return back()->with('success', __('Packaging updated successfully.'));
    }

    public function destroy(Packaging $packaging)
    {
        $packaging->delete();
        return back()->with('success', __('Packaging deleted successfully.'));
    }

    public function addStock(Packaging $packaging)
    {
        $packaging->increment('stock');
        return back()->with('success', __('Stock added.'));
    }

    public function removeStock(Packaging $packaging)
    {
        if ($packaging->stock > 0) {
            $packaging->decrement('stock');
            return back()->with('success', __('Stock removed.'));
        }
        return back()->with('error', __('Stock cannot be less than zero.'));
    }
}
