<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        $period = $request->query('period', 'this_month');

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'kpis' => $dashboardService->getKpis($period),
                'revenueVsCosts' => $dashboardService->getRevenueVsCostsByMonth($period),
                'costsByCategory' => $dashboardService->getCostsByCategory($period),
                'revenueByProduct' => $dashboardService->getRevenueByProduct($period),
                'profitabilityTimeline' => $dashboardService->getProfitabilityTimeline($period),
                'operationalStats' => $dashboardService->getOperationalStats(),
                'ordersByStatus' => $dashboardService->getOrdersByStatus(),
                'recentOrders' => $dashboardService->getRecentOrders(),
                'topProducts' => $dashboardService->getTopProducts()
            ],
            'filters' => [
                'period' => $period
            ]
        ]);
    }
}
