<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Order;
use App\Models\Filament;
use App\Models\Printer;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $notifications = [];

        if ($user) {
            // Active orders (received + printing)
            $activeOrdersCount = Order::whereIn('status', ['received', 'printing'])->count();

            // Filaments with stock = 0
            $emptyFilamentsCount = Filament::where('stock_rolls', 0)->count();

            // Printers: nozzle > 500h or needs maintenance
            $printers = Printer::with('maintenances')->get();
            $printerWarnings = $printers->filter(function ($printer) {
                return $printer->needs_maintenance || $printer->current_nozzle_hours > 500;
            })->count();

            $notifications = [
                'activeOrders'    => $activeOrdersCount,
                'emptyFilaments'  => $emptyFilamentsCount,
                'printerWarnings' => $printerWarnings,
                'total'           => ($activeOrdersCount > 0 ? 1 : 0) + ($emptyFilamentsCount > 0 ? 1 : 0) + ($printerWarnings > 0 ? 1 : 0),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user'                  => $user,
                'hasPrinterNotifications' => $user && ($notifications['printerWarnings'] ?? 0) > 0,
            ],
            'notifications' => $notifications,
        ];
    }
}
