<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FilamentController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvestmentCategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\CalibrationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('orders.index');
});

Route::middleware('auth')->group(function () {
    // Localization route
    Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shared resources (Policies control deeper access)
    Route::resource('orders', OrderController::class);
    
    // Product trash routes
    Route::get('products/trash', [ProductController::class, 'trash'])->name('products.trash');
    Route::post('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore')->withTrashed();
    Route::delete('products/{product}/force', [ProductController::class, 'forceDelete'])->name('products.forceDelete')->withTrashed();
    Route::get('products/{product}/download/{index?}', [ProductController::class, 'download'])->name('products.download');
    Route::resource('products', ProductController::class);
    
    Route::get('filaments/stock', [FilamentController::class, 'stock'])->name('filaments.stock');
    Route::post('filaments/{filament}/stock', [FilamentController::class, 'updateStock'])->name('filaments.update-stock');
    Route::resource('filaments', FilamentController::class);
    Route::resource('calibrations', CalibrationController::class);
    Route::resource('printers', \App\Http\Controllers\PrinterController::class)->except(['create', 'edit']);
    Route::post('printers/{printer}/reset-nozzle', [\App\Http\Controllers\PrinterController::class, 'resetNozzle'])->name('printers.reset-nozzle');
    Route::post('printers/{printer}/maintenance', [\App\Http\Controllers\PrinterController::class, 'storeMaintenance'])->name('printers.maintenance');

    Route::post('packagings/{packaging}/add-stock', [\App\Http\Controllers\PackagingController::class, 'addStock'])->name('packagings.add-stock');
    Route::post('packagings/{packaging}/remove-stock', [\App\Http\Controllers\PackagingController::class, 'removeStock'])->name('packagings.remove-stock');
    Route::resource('packagings', \App\Http\Controllers\PackagingController::class)->except(['create', 'edit', 'show']);
    // Profile (default breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::resource('investments/categories', InvestmentCategoryController::class, ['as' => 'investments']);
        Route::resource('investments', InvestmentController::class);
        Route::resource('users', UserController::class);

        Route::get('/settings', function() {
            return Inertia::render('Settings/Index', [
                'settings' => \App\Models\Setting::pluck('value', 'key')->toArray()
            ]);
        })->name('settings.index');

        Route::post('/settings', function(\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'app_name' => 'nullable|string',
                'default_locale' => 'nullable|string|in:en,sr'
            ]);
            
            foreach ($data as $key => $val) {
                \App\Models\Setting::set($key, $val);
            }
            return back()->with('success', 'Settings updated.');
        })->name('settings.store');
    });
});

require __DIR__.'/auth.php';
