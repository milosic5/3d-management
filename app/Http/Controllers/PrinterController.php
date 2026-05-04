<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PrinterController extends Controller
{
    public function index()
    {
        // Eager load maintenance to calculate the appended attribute efficiently if possible, but actually `latest()->get()` is fine 
        // since we check it dynamically. To avoid N+1, we can eager load maintenances.
        return Inertia::render('Printers/Index', [
            'printers' => \App\Models\Printer::with('maintenances')->latest()->get()
        ]);
    }

    public function show(\App\Models\Printer $printer)
    {
        $printer->load([
            'maintenances' => fn($q) => $q->latest('maintenance_month'),
            'nozzleChanges' => fn($q) => $q->latest()
        ]);

        return Inertia::render('Printers/Show', [
            'printer' => $printer
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_working_hours' => 'required|integer|min:0',
        ]);

        $validated['last_nozzle_working_hours'] = $validated['total_working_hours'];

        \App\Models\Printer::create($validated);
        return back();
    }

    public function update(Request $request, \App\Models\Printer $printer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_working_hours' => 'required|integer|min:0',
        ]);

        $printer->update($validated);
        return back();
    }

    public function resetNozzle(Request $request, \App\Models\Printer $printer)
    {
        $validated = $request->validate([
            'working_hours_at_change' => 'required|integer|min:0',
            'nozzle_diameter' => 'required|numeric',
        ]);

        \App\Models\PrinterNozzleChange::create([
            'printer_id' => $printer->id,
            'nozzle_diameter' => $validated['nozzle_diameter'],
            'working_hours_at_change' => $validated['working_hours_at_change'],
        ]);

        $updateData = [
            'last_nozzle_change_date' => now(),
            'last_nozzle_working_hours' => $validated['working_hours_at_change'],
            'current_nozzle_diameter' => $validated['nozzle_diameter'],
        ];

        if ($validated['working_hours_at_change'] > $printer->total_working_hours) {
            $updateData['total_working_hours'] = $validated['working_hours_at_change'];
        }

        $printer->update($updateData);

        return back();
    }

    public function storeMaintenance(Request $request, \App\Models\Printer $printer)
    {
        $validated = $request->validate([
            'working_hours_at_maintenance' => 'required|integer|min:' . $printer->total_working_hours,
            'lubricated' => 'required|boolean',
        ]);

        $now = now();
        if ($now->day >= 28) {
            $targetMonth = $now->copy()->addMonth()->startOfMonth();
        } else {
            $targetMonth = $now->copy()->startOfMonth();
        }

        $hoursPrintedThisMonth = $validated['working_hours_at_maintenance'] - $printer->total_working_hours;

        \App\Models\PrinterMaintenance::create([
            'printer_id' => $printer->id,
            'maintenance_month' => $targetMonth->format('Y-m-d'),
            'working_hours_at_maintenance' => $validated['working_hours_at_maintenance'],
            'hours_printed_this_month' => $hoursPrintedThisMonth,
            'lubricated' => $validated['lubricated'],
        ]);

        $printer->update([
            'total_working_hours' => $validated['working_hours_at_maintenance'],
        ]);

        return back();
    }

    public function destroy(\App\Models\Printer $printer)
    {
        $printer->delete();
        return back();
    }
}
