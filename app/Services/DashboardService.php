<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Investment;
use App\Models\Order;
use App\Models\Product;
use App\Support\Sql;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(private FinanceReportService $finance) {}

    public function getKpis($period)
    {
        [$start, $end] = $this->finance->dateRange($period);
        $delivered = OrderStatus::Delivered->value;
        $active = array_map(fn (OrderStatus $status) => $status->value, OrderStatus::active());

        // One pass over orders (soft-deleted excluded by the model scope); active orders ignore the period.
        $orders = Order::query()->toBase()->selectRaw(
            'SUM(CASE WHEN status = ? AND created_at BETWEEN ? AND ? THEN total_price ELSE 0 END) AS revenue, '.
            'SUM(CASE WHEN status = ? AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) AS delivered_count, '.
            'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) AS total_orders, '.
            'SUM(CASE WHEN status IN ('.implode(', ', array_fill(0, count($active), '?')).') THEN 1 ELSE 0 END) AS active_orders',
            [$delivered, $start, $end, $delivered, $start, $end, $start, $end, ...$active],
        )->first();

        $revenue = $orders->revenue ?: 0;
        $costs = Investment::whereBetween('invested_at', [$start, $end])->sum('amount');
        $profit = $revenue - $costs;
        $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
        $activeOrders = (int) $orders->active_orders;
        $deliveredCount = (int) $orders->delivered_count;
        $totalOrders = (int) $orders->total_orders;
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
        return $this->finance->revenueVsCosts(...$this->finance->dateRange($period));
    }

    public function getCostsByCategory($period)
    {
        [$start, $end] = $this->finance->dateRange($period);

        return Investment::join('investment_categories', 'investments.category_id', '=', 'investment_categories.id')
            ->select('investment_categories.name', DB::raw('SUM(investments.amount) as total'))
            ->whereBetween('investments.invested_at', [$start, $end])
            ->groupBy('investment_categories.name')
            ->get()
            ->toArray();
    }

    public function getRevenueByProduct($period)
    {
        [$start, $end] = $this->finance->dateRange($period);
        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNull('orders.deleted_at')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', 'products.color_hex', DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'))
            ->where('orders.status', OrderStatus::Delivered)
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name', 'products.color_hex')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();

        return $this->finance->groupCepovi($rows)
            ->sortByDesc('revenue')
            ->take(8)
            ->values()
            ->toArray();
    }

    public function getProfitabilityTimeline($period)
    {
        [$start, $end] = $this->finance->dateRange($period);

        return DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->select(
                DB::raw(Sql::yearMonth('created_at').' as month'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw(Sql::yearMonth('created_at'))
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
            ->whereNull('orders.deleted_at')
            ->where('orders.status', OrderStatus::Delivered)
            ->select(
                DB::raw('COUNT(DISTINCT orders.id) as total_prints'),
                DB::raw('SUM(order_items.print_time_minutes) as total_minutes'),
                DB::raw('SUM(order_items.weight_grams) as total_grams')
            )->first();

        $queueMinutes = Order::whereIn('status', OrderStatus::active())->sum('estimated_print_minutes');

        return [
            'totalProducts' => $activeProducts,
            'avgProductPrice' => round((float) $avgPrice, 2),
            'totalPrints' => (int) ($deliveredItems->total_prints ?? 0),
            'totalPrintHours' => round(strval($deliveredItems->total_minutes ?? 0) / 60, 1),
            'totalFilamentKg' => round(strval($deliveredItems->total_grams ?? 0) / 1000, 2),
            'queueMinutes' => (int) $queueMinutes,
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
        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNull('orders.deleted_at')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'products.color_hex',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('AVG(order_items.unit_price) as avg_price'),
                DB::raw('SUM(order_items.print_time_minutes) / 60 as print_hours')
            )
            ->where('orders.status', OrderStatus::Delivered)
            ->groupBy('products.id', 'products.name', 'products.color_hex')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();

        return $this->finance->groupCepovi($rows)
            ->sortByDesc('revenue')
            ->take(5)
            ->values();
    }
}
