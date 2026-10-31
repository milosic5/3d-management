<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Filament;
use App\Models\Order;
use App\Models\Printer;
use Illuminate\Support\Facades\Cache;

/**
 * Sidebar counters shared on every Inertia request. They are the same for every user,
 * so they are cached and forgotten whenever an order, filament or printer changes
 * (see AppServiceProvider). The TTL covers the date-based maintenance switch on the 28th.
 */
class NavigationNotifications
{
    public const CACHE_KEY = 'navigation-notifications';

    /**
     * @return array{activeOrders: int, emptyFilaments: int, printerWarnings: int, total: int}
     */
    public static function get(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), fn () => self::compute());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function compute(): array
    {
        $activeOrders = Order::whereIn('status', OrderStatus::active())->count();

        $emptyFilaments = Filament::where('stock_rolls', 0)->count();

        // Nozzle over 500h or monthly maintenance missing.
        $printerWarnings = Printer::with('maintenances')->get()
            ->filter(fn (Printer $printer) => $printer->needs_maintenance || $printer->current_nozzle_hours > 500)
            ->count();

        return [
            'activeOrders' => $activeOrders,
            'emptyFilaments' => $emptyFilaments,
            'printerWarnings' => $printerWarnings,
            'total' => ($activeOrders > 0 ? 1 : 0) + ($emptyFilaments > 0 ? 1 : 0) + ($printerWarnings > 0 ? 1 : 0),
        ];
    }
}
