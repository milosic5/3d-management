<?php

namespace App\Http\Controllers;

use App\Models\InvestmentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Investments/Categories/Index', [
            'categories' => InvestmentCategory::withCount('investments')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Investment::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:equipment,consumable,utility,other'
        ]);

        InvestmentCategory::create($validated);
        return back();
    }

    public function destroy(InvestmentCategory $category)
    {
        $this->authorize('delete', \App\Models\Investment::class);
        
        if ($category->investments()->exists()) {
            return back()->with('error', 'Cannot delete category with linked investments.');
        }

        $category->delete();
        return back();
    }
}
