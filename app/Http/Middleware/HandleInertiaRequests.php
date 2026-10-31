<?php

namespace App\Http\Middleware;

use App\Enums\FilamentMaterial;
use App\Enums\OrderStatus;
use App\Models\Calibration;
use App\Models\Filament;
use App\Models\Order;
use App\Models\Packaging;
use App\Models\Printer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\NavigationNotifications;
use Illuminate\Http\Request;
use Inertia\Middleware;

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

        $notifications = $user ? NavigationNotifications::get() : [];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'hasPrinterNotifications' => $user && ($notifications['printerWarnings'] ?? 0) > 0,
                'can' => $user ? $this->abilities($user) : [],
            ],
            'notifications' => $notifications,
            'appName' => fn () => Setting::appName(),
            // Lazy: SetLocale runs after this middleware, so resolve the locale at render time.
            'locale' => fn () => app()->getLocale(),
            'enums' => [
                'orderStatuses' => OrderStatus::options(),
                'materials' => FilamentMaterial::options(),
            ],
        ];
    }

    /**
     * Permissions the frontend needs to show or hide actions (resolved through policies).
     *
     * @return array<string, bool>
     */
    private function abilities(User $user): array
    {
        return [
            'deleteOrders' => $user->can('delete', Order::class),
            'manageOrderTrash' => $user->can('viewTrashed', Order::class),
            'deleteProducts' => $user->can('delete', Product::class),
            'manageProductTrash' => $user->can('viewTrashed', Product::class),
            'deleteFilaments' => $user->can('delete', Filament::class),
            'deletePrinters' => $user->can('delete', Printer::class),
            'deleteMaintenances' => $user->can('deleteMaintenance', Printer::class),
            'deletePackagings' => $user->can('delete', Packaging::class),
            'deleteCalibrations' => $user->can('delete', Calibration::class),
        ];
    }
}
