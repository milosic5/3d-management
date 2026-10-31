<?php

namespace App\Providers;

use App\Models\Filament;
use App\Models\Investment;
use App\Models\InvestmentCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Printer;
use App\Models\PrinterMaintenance;
use App\Models\Product;
use App\Support\NavigationNotifications;
use App\Support\ReportCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        Gate::before(function ($user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        $this->registerCacheInvalidation();
    }

    /**
     * Forget cached counters and reports as soon as the data behind them changes.
     */
    private function registerCacheInvalidation(): void
    {
        $watch = function (array $models, callable $forget) {
            foreach ($models as $model) {
                $model::saved(fn () => $forget());
                $model::deleted(fn () => $forget());

                if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                    $model::restored(fn () => $forget());
                }
            }
        };

        $watch(
            [Order::class, Filament::class, Printer::class, PrinterMaintenance::class],
            fn () => NavigationNotifications::forget(),
        );

        $watch(
            [Order::class, OrderItem::class, Product::class, Investment::class, InvestmentCategory::class],
            fn () => ReportCache::flush(),
        );
    }
}
