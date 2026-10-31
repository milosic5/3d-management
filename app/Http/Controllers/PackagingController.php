<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackagingRequest;
use App\Http\Requests\UpdatePackagingRequest;
use App\Models\Packaging;
use Inertia\Inertia;

class PackagingController extends Controller
{
    public function index()
    {
        $packagings = Packaging::latest()->get();

        return Inertia::render('Packaging/Index', [
            'packagings' => $packagings,
        ]);
    }

    public function store(StorePackagingRequest $request)
    {
        $validated = $request->validated();

        if ($validated['type'] === 'envelope') {
            $validated['height'] = null;
        }

        Packaging::create($validated);

        return back()->with('success', __('Packaging created successfully.'));
    }

    public function update(UpdatePackagingRequest $request, Packaging $packaging)
    {
        $validated = $request->validated();

        if ($packaging->type === 'envelope') {
            $validated['height'] = null;
        }

        $packaging->update($validated);

        return back()->with('success', __('Packaging updated successfully.'));
    }

    public function destroy(Packaging $packaging)
    {
        $this->authorize('delete', $packaging);

        $packaging->delete();

        return back()->with('success', __('Packaging deleted successfully.'));
    }

    public function addStock(Packaging $packaging)
    {
        $this->authorize('update', $packaging);

        $packaging->increment('stock');

        return back()->with('success', __('Stock added.'));
    }

    public function removeStock(Packaging $packaging)
    {
        $this->authorize('update', $packaging);

        if ($packaging->stock > 0) {
            $packaging->decrement('stock');

            return back()->with('success', __('Stock removed.'));
        }

        return back()->with('error', __('Stock cannot be less than zero.'));
    }
}
