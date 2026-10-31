<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Services\FinanceReportService;
use App\Support\Sql;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(private FinanceReportService $finance) {}

    public function index(Request $request): Response
    {
        $period = $request->query('period', 'this_year');
        [$start, $end] = $this->finance->dateRange($period);

        return Inertia::render('Analytics/Index', [
            'revenueVsCosts' => $this->finance->revenueVsCosts($start, $end),
            'topCustomers' => $this->getTopCustomers($start, $end),
            'materialBreakdown' => $this->getMaterialBreakdown($start, $end),
            'productProfitability' => $this->getProductProfitability($start, $end),
            'monthlyOrders' => $this->getMonthlyOrders($start, $end),
            'filters' => ['period' => $period],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Top customers by total spend (delivered orders only).
     */
    private function getTopCustomers(Carbon $start, Carbon $end): array
    {
        return DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->select(
                'customer_name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price) as total_spent'),
                DB::raw('AVG(total_price) as avg_order_value')
            )
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('customer_name')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Revenue breakdown by material (PLA vs PETG etc.).
     *
     * Material comes from the filament printed on the order item; older items without one
     * fall back to the product's filament, then the legacy products.material. Unknown → null.
     */
    private function getMaterialBreakdown(Carbon $start, Carbon $end): array
    {
        $material = 'COALESCE(item_filaments.material, product_filaments.material, products.material)';

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNull('orders.deleted_at')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('filaments as item_filaments', 'order_items.filament_id', '=', 'item_filaments.id')
            ->leftJoin('filaments as product_filaments', 'products.filament_id', '=', 'product_filaments.id')
            ->select(
                DB::raw("{$material} as material"),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('SUM(order_items.weight_grams) as total_grams'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->where('orders.status', OrderStatus::Delivered)
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupByRaw($material)
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    /**
     * Product profitability: revenue, filament cost, gross margin per product.
     */
    private function getProductProfitability(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNull('orders.deleted_at')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('filaments', 'order_items.filament_id', '=', 'filaments.id')
            ->select(
                'products.id',
                'products.name',
                'products.color_hex',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('SUM(order_items.print_cost) as print_costs'),
                DB::raw('SUM(
                    CASE WHEN filaments.price_per_kg IS NOT NULL
                    THEN (order_items.weight_grams / 1000.0) * filaments.price_per_kg
                    ELSE 0 END
                ) as filament_cost'),
                DB::raw('SUM(order_items.weight_grams) as total_grams'),
                DB::raw('SUM(order_items.print_time_minutes) as total_minutes')
            )
            ->where('orders.status', OrderStatus::Delivered)
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name', 'products.color_hex')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();

        return $this->finance->groupCepovi($rows)
            ->sortByDesc('revenue')
            ->take(15)
            ->map(function ($row) {
                $row = (object) $row;
                $revenue = (float) ($row->revenue ?? 0);
                $costs = (float) ($row->print_costs ?? 0) + (float) ($row->filament_cost ?? 0);
                $profit = $revenue - $costs;
                $margin = $revenue > 0 ? round($profit / $revenue * 100, 1) : 0;

                return array_merge((array) $row, [
                    'gross_profit' => $profit,
                    'margin' => $margin,
                ]);
            })
            ->values()
            ->toArray();
    }

    /**
     * Monthly order count (all statuses, for trend chart).
     */
    private function getMonthlyOrders(Carbon $start, Carbon $end): array
    {
        return DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->select(
                DB::raw(Sql::yearMonth('created_at').' as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='".OrderStatus::Delivered->value."' THEN 1 ELSE 0 END) as delivered"),
                DB::raw("SUM(CASE WHEN status='".OrderStatus::Cancelled->value."' THEN 1 ELSE 0 END) as cancelled")
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw(Sql::yearMonth('created_at'))
            ->orderBy('month')
            ->get()
            ->toArray();
    }
}
