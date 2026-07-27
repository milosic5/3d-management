<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Investment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected function getDateRange($period)
    {
        $now = Carbon::now();
        if (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $date = Carbon::createFromFormat('Y-m', $period);
            return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
        }
        
        return match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [Carbon::create(2000, 1, 1), $now->copy()->endOfDay()], // all_time
        };
    }

    public function getKpis($period)
    {
        [$start, $end] = $this->getDateRange($period);
        $revenue = Order::where('status', 'delivered')->whereBetween('created_at', [$start, $end])->sum('total_price');
        $costs = Investment::whereBetween('invested_at', [$start, $end])->sum('amount');
        $profit = $revenue - $costs;
        $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
        $activeOrders = Order::whereIn('status', ['received', 'printing'])->count();
        $deliveredCount = Order::where('status', 'delivered')->whereBetween('created_at', [$start, $end])->count();
        $totalOrders = Order::whereBetween('created_at', [$start, $end])->count();
        $avgOrderValue = $deliveredCount > 0 ? round($revenue / $deliveredCount, 2) : 0;

        return [
            'totalRevenue' => $revenue,
            'totalCosts' => $costs,
            'grossProfit' => $profit,
            'profitMargin' => $margin,
            'activeOrders' => $activeOrders,
            'avgOrderValue' => $avgOrderValue,
            'totalOrders' => $totalOrders,
        ];
    }

    public function getRevenueVsCostsByMonth($period)
    {
        [$start, $end] = $this->getDateRange($period);

        $revenue = DB::table('orders')
            ->select(DB::raw("strftime('%Y-%m', created_at) as month"), DB::raw('SUM(total_price) as revenue'))
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("strftime('%Y-%m', created_at)")
            ->orderBy('month')
            ->get()->keyBy('month');

        $costs = DB::table('investments')
            ->select(DB::raw("strftime('%Y-%m', invested_at) as month"), DB::raw('SUM(amount) as costs'))
            ->whereBetween('invested_at', [$start, $end])
            ->groupByRaw("strftime('%Y-%m', invested_at)")
            ->orderBy('month')
            ->get()->keyBy('month');

        $months = collect($revenue->keys())->merge($costs->keys())->unique()->sort()->values();

        return $months->map(fn($m) => [
            'month'   => $m,
            'revenue' => (float) ($revenue[$m]->revenue ?? 0),
            'costs'   => (float) ($costs[$m]->costs   ?? 0),
            'profit'  => (float) ($revenue[$m]->revenue ?? 0) - (float) ($costs[$m]->costs ?? 0),
        ])->values()->toArray();
    }

    public function getCostsByCategory($period)
    {
        [$start, $end] = $this->getDateRange($period);
        return Investment::join('investment_categories', 'investments.category_id', '=', 'investment_categories.id')
            ->select('investment_categories.name', DB::raw('SUM(investments.amount) as total'))
            ->whereBetween('investments.invested_at', [$start, $end])
            ->groupBy('investment_categories.name')
            ->get()
            ->toArray();
    }

    public function getRevenueByProduct($period)
    {
        [$start, $end] = $this->getDateRange($period);
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', 'products.color_hex', DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'))
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name', 'products.color_hex')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function getProfitabilityTimeline($period)
    {
        [$start, $end] = $this->getDateRange($period);

        return DB::table('orders')
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("strftime('%Y-%m', created_at)")
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function getOperationalStats()
    {
        $activeProducts = Product::where('is_active', true)->count();
        $avgPrice = Product::where('is_active', true)->avg('price');
        
        $deliveredItems = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->select(
                DB::raw('COUNT(DISTINCT orders.id) as total_prints'),
                DB::raw('SUM(order_items.print_time_minutes) as total_minutes'),
                DB::raw('SUM(order_items.weight_grams) as total_grams')
            )->first();
            
        $queueMinutes = Order::whereIn('status', ['received', 'printing'])->sum('estimated_print_minutes');

        return [
            'totalProducts' => $activeProducts,
            'avgProductPrice' => round((float)$avgPrice, 2),
            'totalPrints' => (int)($deliveredItems->total_prints ?? 0),
            'totalPrintHours' => round(strval($deliveredItems->total_minutes ?? 0) / 60, 1),
            'totalFilamentKg' => round(strval($deliveredItems->total_grams ?? 0) / 1000, 2),
            'queueMinutes' => (int)$queueMinutes,
        ];
    }

    public function getOrdersByStatus()
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getRecentOrders()
    {
        return Order::with('items')->latest()->limit(10)->get();
    }

    public function getTopProducts()
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name', 
                'products.color_hex',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('AVG(order_items.unit_price) as avg_price'),
                DB::raw('SUM(order_items.print_time_minutes) / 60 as print_hours')
            )
            ->where('orders.status', 'delivered')
            ->groupBy('products.id', 'products.name', 'products.color_hex')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
    }
}
