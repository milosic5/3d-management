<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentCategoryRequest;
use App\Models\Investment;
use App\Models\InvestmentCategory;
use Inertia\Inertia;

class InvestmentCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Investments/Categories/Index', [
            'categories' => InvestmentCategory::withCount('investments')->get(),
        ]);
    }

    public function store(StoreInvestmentCategoryRequest $request)
    {
        InvestmentCategory::create($request->validated());

        return back();
    }

    public function destroy(InvestmentCategory $category)
    {
        $this->authorize('delete', Investment::class);

        if ($category->investments()->exists()) {
            return back()->with('error', 'Cannot delete category with linked investments.');
        }

        $category->delete();

        return back();
    }
}
