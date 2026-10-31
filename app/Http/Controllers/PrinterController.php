<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetNozzleRequest;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\StorePrinterRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Http\Requests\UpdatePrinterRequest;
use App\Models\Printer;
use App\Models\PrinterMaintenance;
use App\Models\PrinterNozzleChange;
use Inertia\Inertia;

class PrinterController extends Controller
{
    public function index()
    {
        // Maintenances are eager loaded so needs_maintenance needs no query per printer.
        return Inertia::render('Printers/Index', [
            'printers' => Printer::with('maintenances')->latest()->get()
                ->each->append(['current_nozzle_hours', 'needs_maintenance']),
        ]);
    }

    public function show(Printer $printer)
    {
        $printer->load([
            'maintenances' => fn ($q) => $q->latest('maintenance_month'),
            'nozzleChanges' => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('Printers/Show', [
            'printer' => $printer,
        ]);
    }

    public function store(StorePrinterRequest $request)
    {
        $validated = $request->validated();
        $validated['last_nozzle_working_hours'] = $validated['total_working_hours'];

        Printer::create($validated);

        return back();
    }

    public function update(UpdatePrinterRequest $request, Printer $printer)
    {
        $printer->update($request->validated());

        return back();
    }

    public function resetNozzle(ResetNozzleRequest $request, Printer $printer)
    {
        $validated = $request->validated();

        PrinterNozzleChange::create([
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

    public function storeMaintenance(StoreMaintenanceRequest $request, Printer $printer)
    {
        $validated = $request->validated();

        $now = now();
        if ($now->day <= 15) {
            $targetMonth = $now->copy()->subMonth()->startOfMonth();
        } else {
            $targetMonth = $now->copy()->startOfMonth();
        }

        $hoursPrintedThisMonth = $validated['working_hours_at_maintenance'] - $printer->total_working_hours;

        $maintenance = PrinterMaintenance::where('printer_id', $printer->id)
            ->where('maintenance_month', $targetMonth->format('Y-m-d'))
            ->first();

        if ($maintenance) {
            $maintenance->update([
                'working_hours_at_maintenance' => $validated['working_hours_at_maintenance'],
                'hours_printed_this_month' => $maintenance->hours_printed_this_month + $hoursPrintedThisMonth,
                'lubricated' => $validated['lubricated'],
            ]);
        } else {
            PrinterMaintenance::create([
                'printer_id' => $printer->id,
                'maintenance_month' => $targetMonth->format('Y-m-d'),
                'working_hours_at_maintenance' => $validated['working_hours_at_maintenance'],
                'hours_printed_this_month' => $hoursPrintedThisMonth,
                'lubricated' => $validated['lubricated'],
            ]);
        }

        $printer->update([
            'total_working_hours' => $validated['working_hours_at_maintenance'],
        ]);

        return back();
    }

    public function destroy(Printer $printer)
    {
        $this->authorize('delete', $printer);

        $printer->delete();

        return back();
    }

    public function updateMaintenance(UpdateMaintenanceRequest $request, PrinterMaintenance $maintenance)
    {
        $validated = $request->validated();

        $validated['maintenance_month'] .= '-01';

        $maintenance->update($validated);

        return back();
    }

    public function destroyMaintenance(PrinterMaintenance $maintenance)
    {
        $this->authorize('deleteMaintenance', $maintenance->printer);

        $maintenance->delete();

        return back();
    }
}
