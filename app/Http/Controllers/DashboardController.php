<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\ReportCache;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        $period = $request->query('period', 'this_month');

        // Relative periods depend on today's date, so it is part of the key; data changes flush it.
        $cacheKey = 'dashboard.'.md5($period).'.'.now()->toDateString();

        $stats = ReportCache::remember($cacheKey, fn () => [
            'kpis' => $dashboardService->getKpis($period),
            'revenueVsCosts' => $dashboardService->getRevenueVsCostsByMonth($period),
            'costsByCategory' => $dashboardService->getCostsByCategory($period),
            'revenueByProduct' => $dashboardService->getRevenueByProduct($period),
            'profitabilityTimeline' => $dashboardService->getProfitabilityTimeline($period),
            'operationalStats' => $dashboardService->getOperationalStats(),
            'ordersByStatus' => $dashboardService->getOrdersByStatus(),
            'recentOrders' => $dashboardService->getRecentOrders()->toArray(),
            'topProducts' => $dashboardService->getTopProducts(),
        ]);

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'filters' => [
                'period' => $period,
            ],
        ]);
    }
}
