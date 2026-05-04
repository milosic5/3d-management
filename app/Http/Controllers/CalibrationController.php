<?php

namespace App\Http\Controllers;

use App\Models\Calibration;
use App\Models\Filament;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CalibrationController extends Controller
{
    public function index(Request $request)
    {
        // Top 5 brands logic
        $topBrands = DB::table('calibrations')
            ->join('filaments', 'calibrations.filament_id', '=', 'filaments.id')
            ->select('filaments.brand', DB::raw('count(*) as total'))
            ->groupBy('filaments.brand')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('brand');

        $query = Calibration::query()
            ->select('calibrations.*', 'filaments.brand', 'filaments.name as filament_name', 'filaments.color_name', 'filaments.color_hex')
            ->join('filaments', 'calibrations.filament_id', '=', 'filaments.id');

        // Toggle Buttons Filtering (Brands)
        if ($request->has('brands') && is_array($request->brands)) {
            $brands = $request->brands;
            if (in_array('others', $brands)) {
                $query->where(function ($q) use ($brands, $topBrands) {
                    // Extract non-'others' brands that the user checked
                    $checkedKnownBrands = array_diff($brands, ['others']);
                    
                    if (count($checkedKnownBrands) > 0) {
                        $q->whereIn('filaments.brand', $checkedKnownBrands)
                          ->orWhereNotIn('filaments.brand', $topBrands);
                    } else {
                        // User only checked 'others'
                        $q->whereNotIn('filaments.brand', $topBrands);
                    }
                });
            } else {
                $query->whereIn('filaments.brand', $brands);
            }
        }

        // Sorting
        $sortColumn = $request->input('sort', 'calibrations.created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Map column aliases to real columns
        $sortMap = [
            'brand' => 'filaments.brand',
            'filament_name' => 'filaments.name',
            'color_name' => 'filaments.color_name',
            'temperature' => 'calibrations.temperature',
            'flow_ratio' => 'calibrations.flow_ratio',
            'pressure_advance' => 'calibrations.pressure_advance',
            'max_volumetric_speed' => 'calibrations.max_volumetric_speed',
        ];

        $orderCol = $sortMap[$sortColumn] ?? 'calibrations.created_at';
        $query->orderBy($orderCol, $sortDirection);

        $calibrations = $query->paginate(15)->withQueryString();

        return Inertia::render('Calibrations/Index', [
            'calibrations' => $calibrations,
            'topBrands' => $topBrands,
            'filters' => $request->only(['brands', 'sort', 'direction'])
        ]);
    }

    public function create()
    {
        // Get all filaments that don't already have a calibration
        $availableFilaments = Filament::whereDoesntHave('calibration')
            ->orderBy('brand')
            ->orderBy('name')
            ->get();

        return Inertia::render('Calibrations/Create', [
            'availableFilaments' => $availableFilaments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filament_id' => 'required|exists:filaments,id|unique:calibrations,filament_id',
            'temperature' => 'required|integer',
            'flow_ratio' => 'required|numeric',
            'pressure_advance' => 'required|numeric',
            'max_volumetric_speed' => 'required|numeric'
        ]);

        Calibration::create($validated);
        return redirect()->route('calibrations.index')->with('success', 'Calibration saved successfully.');
    }

    public function edit(Calibration $calibration)
    {
        // Make sure to retrieve the filament that this calibration belongs to
        // and add it to the available filaments so it can be selected in the edit dropdown
        $calibration->load('filament');
        
        $availableFilaments = Filament::whereDoesntHave('calibration')
            ->orWhere('id', $calibration->filament_id)
            ->orderBy('brand')
            ->orderBy('name')
            ->get();

        return Inertia::render('Calibrations/Edit', [
            'calibration' => $calibration,
            'availableFilaments' => $availableFilaments
        ]);
    }

    public function update(Request $request, Calibration $calibration)
    {
        $validated = $request->validate([
            'filament_id' => 'required|exists:filaments,id|unique:calibrations,filament_id,' . $calibration->id,
            'temperature' => 'required|integer',
            'flow_ratio' => 'required|numeric',
            'pressure_advance' => 'required|numeric',
            'max_volumetric_speed' => 'required|numeric'
        ]);

        $calibration->update($validated);
        return redirect()->route('calibrations.index')->with('success', 'Calibration updated successfully.');
    }

    public function destroy(Calibration $calibration)
    {
        $calibration->delete();
        return redirect()->route('calibrations.index')->with('success', 'Calibration deleted successfully.');
    }
}
