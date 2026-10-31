<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentRequest;
use App\Http\Requests\UpdateInvestmentRequest;
use App\Models\Filament;
use App\Models\Investment;
use App\Models\InvestmentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Investment::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }
        if ($category = $request->input('category')) {
            $query->where('category_id', $category);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('invested_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('invested_at', '<=', $to);
        }

        $totalInvested = $query->sum('amount');

        // Sorting
        $allowedSorts = ['invested_at', 'name', 'amount', 'quantity'];
        $sortKey = $request->input('sort');
        $sortDir = $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sortKey && in_array($sortKey, $allowedSorts)) {
            $query->orderBy($sortKey, $sortDir);
        } else {
            $query->latest('invested_at');
        }

        return Inertia::render('Investments/Index', [
            'investments' => $query->paginate(15)->withQueryString(),
            'categories' => InvestmentCategory::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'category', 'from', 'to', 'sort', 'dir']),
            'summary' => [
                'totalInvested' => $totalInvested,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Investments/Create', [
            'categories' => InvestmentCategory::all(),
            'filaments' => $this->filamentOptions(),
        ]);
    }

    public function store(StoreInvestmentRequest $request)
    {
        $validated = $request->validated();

        $validated['amount'] = $validated['unit_cost'] * ($validated['quantity'] ?: 1);
        $validated['created_by'] = $request->user()->id;

        Investment::create($validated);

        return redirect()->route('investments.index');
    }

    public function edit(Investment $investment)
    {
        return Inertia::render('Investments/Edit', [
            'investment' => $investment,
            'categories' => InvestmentCategory::all(),
            'filaments' => $this->filamentOptions(),
        ]);
    }

    private function filamentOptions()
    {
        return Filament::orderBy('brand')->orderBy('name')
            ->get(['id', 'brand', 'name', 'color_name', 'price_per_kg']);
    }

    public function update(UpdateInvestmentRequest $request, Investment $investment)
    {
        $validated = $request->validated();

        $validated['amount'] = $validated['unit_cost'] * ($validated['quantity'] ?: 1);

        $investment->update($validated);

        return redirect()->route('investments.index');
    }

    public function destroy(Investment $investment)
    {
        $this->authorize('delete', $investment);
        $investment->delete();

        return redirect()->route('investments.index');
    }
}
