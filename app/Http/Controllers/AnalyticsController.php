<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Order;
use App\Models\Investment;
use App\Models\Product;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->query('period', 'this_year');
        [$start, $end] = $this->getDateRange($period);

        return Inertia::render('Analytics/Index', [
            'revenueVsCosts'      => $this->getRevenueVsCosts($start, $end),
            'topCustomers'        => $this->getTopCustomers($start, $end),
            'materialBreakdown'   => $this->getMaterialBreakdown($start, $end),
            'productProfitability'=> $this->getProductProfitability($start, $end),
            'monthlyOrders'       => $this->getMonthlyOrders($start, $end),
            'filters'             => ['period' => $period],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function getDateRange(string $period): array
    {
        $now = Carbon::now();
        if (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $date = Carbon::createFromFormat('Y-m', $period);
            return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
        }
        return match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year'  => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default      => [Carbon::create(2000, 1, 1), $now->copy()->endOfDay()],
        };
    }

    /**
     * Monthly revenue (delivered orders) vs costs (investments) for bar/line chart.
     */
    private function getRevenueVsCosts(Carbon $start, Carbon $end): array
    {
        // Revenue per month
        $revenue = DB::table('orders')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total_price) as revenue'))
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Costs per month
        $costs = DB::table('investments')
            ->select(DB::raw("DATE_FORMAT(invested_at, '%Y-%m') as month"), DB::raw('SUM(amount) as costs'))
            ->whereBetween('invested_at', [$start, $end])
            ->groupByRaw("DATE_FORMAT(invested_at, '%Y-%m')")
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Merge months
        $months = collect($revenue->keys())->merge($costs->keys())->unique()->sort()->values();

        return $months->map(fn($m) => [
            'month'   => $m,
            'revenue' => (float) ($revenue[$m]->revenue ?? 0),
            'costs'   => (float) ($costs[$m]->costs   ?? 0),
            'profit'  => (float) ($revenue[$m]->revenue ?? 0) - (float) ($costs[$m]->costs ?? 0),
        ])->values()->toArray();
    }

    /**
     * Top customers by total spend (delivered orders only).
     */
    private function getTopCustomers(Carbon $start, Carbon $end): array
    {
        return DB::table('orders')
            ->select(
                'customer_name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price) as total_spent'),
                DB::raw('AVG(total_price) as avg_order_value')
            )
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('customer_name')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Revenue breakdown by material (PLA vs PETG etc.).
     */
    private function getMaterialBreakdown(Carbon $start, Carbon $end): array
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.material',
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('SUM(order_items.weight_grams * order_items.quantity) as total_grams'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.material')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    /**
     * Product profitability: revenue, filament cost, gross margin per product.
     */
    private function getProductProfitability(Carbon $start, Carbon $end): array
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('filaments', 'order_items.filament_id', '=', 'filaments.id')
            ->select(
                'products.id',
                'products.name',
                'products.color_hex',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('SUM(order_items.print_cost * order_items.quantity) as print_costs'),
                DB::raw('SUM(
                    CASE WHEN filaments.price_per_kg IS NOT NULL
                    THEN (order_items.weight_grams / 1000.0) * filaments.price_per_kg * order_items.quantity
                    ELSE 0 END
                ) as filament_cost'),
                DB::raw('SUM(order_items.weight_grams * order_items.quantity) as total_grams'),
                DB::raw('SUM(order_items.print_time_minutes * order_items.quantity) as total_minutes')
            )
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name', 'products.color_hex')
            ->orderByDesc('revenue')
            ->limit(15)
            ->get()
            ->map(function ($row) {
                $revenue      = (float) $row->revenue;
                $costs        = (float) $row->print_costs + (float) $row->filament_cost;
                $profit       = $revenue - $costs;
                $margin       = $revenue > 0 ? round($profit / $revenue * 100, 1) : 0;
                return array_merge((array) $row, [
                    'gross_profit' => $profit,
                    'margin'       => $margin,
                ]);
            })
            ->toArray();
    }

    /**
     * Monthly order count (all statuses, for trend chart).
     */
    private function getMonthlyOrders(Carbon $start, Carbon $end): array
    {
        return DB::table('orders')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) as delivered"),
                DB::raw("SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get()
            ->toArray();
    }
}
