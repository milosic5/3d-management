<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FilamentController;
use App\Http\Controllers\InvestmentCategoryController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('orders.index');
});

Route::middleware('auth')->group(function () {
    // Localization route
    Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shared resources (Policies control deeper access)
    // Order trash routes
    Route::get('orders/trash', [OrderController::class, 'trash'])->name('orders.trash');
    Route::post('orders/{order}/restore', [OrderController::class, 'restore'])->name('orders.restore')->withTrashed();
    Route::delete('orders/{order}/force', [OrderController::class, 'forceDelete'])->name('orders.forceDelete')->withTrashed();
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
    Route::resource('printers', PrinterController::class)->except(['create', 'edit']);
    Route::post('printers/{printer}/reset-nozzle', [PrinterController::class, 'resetNozzle'])->name('printers.reset-nozzle');
    Route::post('printers/{printer}/maintenance', [PrinterController::class, 'storeMaintenance'])->name('printers.maintenance');
    Route::put('maintenances/{maintenance}', [PrinterController::class, 'updateMaintenance'])->name('maintenances.update');
    Route::delete('maintenances/{maintenance}', [PrinterController::class, 'destroyMaintenance'])->name('maintenances.destroy');
    Route::post('packagings/{packaging}/add-stock', [PackagingController::class, 'addStock'])->name('packagings.add-stock');
    Route::post('packagings/{packaging}/remove-stock', [PackagingController::class, 'removeStock'])->name('packagings.remove-stock');
    Route::resource('packagings', PackagingController::class)->except(['create', 'edit', 'show']);

    // Export routes
    Route::get('/export/orders/excel', [ExportController::class, 'ordersExcel'])->name('export.orders.excel');
    Route::get('/export/orders/{order}/pdf', [ExportController::class, 'orderPdf'])->name('export.order.pdf');
    Route::middleware('role:admin')->group(function () {
        Route::get('/export/investments/excel', [ExportController::class, 'investmentsExcel'])->name('export.investments.excel');
    });
    // Profile (default breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // No self-delete: only admins delete users (UserController), which protects their orders.

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::resource('investments/categories', InvestmentCategoryController::class, ['as' => 'investments']);
        Route::resource('investments', InvestmentController::class);
        Route::resource('users', UserController::class);

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    });
});

require __DIR__.'/auth.php';
