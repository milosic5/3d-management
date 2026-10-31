<?php

namespace App\Services;

use App\Support\Sql;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Building blocks shared by the dashboard and the analytics page.
 */
class FinanceReportService
{
    /**
     * Resolve a period filter into [start, end].
     *
     * Accepts "this_month", "last_month", "this_year", "last_year" or a "YYYY-MM" month;
     * anything else means all time (from 2000-01-01 up to the end of today).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function dateRange(string $period): array
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
            default => [Carbon::create(2000, 1, 1), $now->copy()->endOfDay()],
        };
    }

    /**
     * Monthly revenue (delivered orders) vs costs (investments), one row per month that has either.
     *
     * @return array<int, array{month: string, revenue: float, costs: float, profit: float}>
     */
    public function revenueVsCosts(Carbon $start, Carbon $end): array
    {
        $revenue = DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->select(DB::raw(Sql::yearMonth('created_at').' as month'), DB::raw('SUM(total_price) as revenue'))
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw(Sql::yearMonth('created_at'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $costs = DB::table('investments')
            ->select(DB::raw(Sql::yearMonth('invested_at').' as month'), DB::raw('SUM(amount) as costs'))
            ->whereBetween('invested_at', [$start, $end])
            ->groupByRaw(Sql::yearMonth('invested_at'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = collect($revenue->keys())->merge($costs->keys())->unique()->sort()->values();

        return $months->map(fn ($m) => [
            'month' => $m,
            'revenue' => (float) ($revenue[$m]->revenue ?? 0),
            'costs' => (float) ($costs[$m]->costs ?? 0),
            'profit' => (float) ($revenue[$m]->revenue ?? 0) - (float) ($costs[$m]->costs ?? 0),
        ])->values()->toArray();
    }

    /**
     * Merge all rows whose name contains "cep" or "čep" (case-insensitive)
     * into a single "Cepovi" entry, summing numeric columns.
     *
     * Other rows are grouped by name and keep only the first row of each name.
     */
    public function groupCepovi(iterable $rows): Collection
    {
        $cepPattern = '/[čc]ep/iu';

        $grouped = collect($rows)->groupBy(function ($row) use ($cepPattern) {
            $name = is_array($row) ? $row['name'] : $row->name;

            return preg_match($cepPattern, $name) ? '__cepovi__' : $name;
        });

        return $grouped->map(function ($items, $key) {
            $first = (array) $items->first();

            if ($key !== '__cepovi__') {
                return $first;
            }

            // Sum all numeric fields (id is kept from the first row); average avg_price.
            $summed = $first;
            $summed['name'] = 'Cepovi';
            $summed['color_hex'] = '#f97316';

            foreach ($first as $col => $val) {
                if ($col === 'name' || $col === 'color_hex' || $col === 'id') {
                    continue;
                }
                if (is_numeric($val)) {
                    $summed[$col] = $items->sum(fn ($i) => (float) ((array) $i)[$col]);
                }
            }

            if (array_key_exists('avg_price', $summed)) {
                $summed['avg_price'] = $items->avg(fn ($i) => (float) ((array) $i)['avg_price']);
            }

            return $summed;
        })->values();
    }
}
