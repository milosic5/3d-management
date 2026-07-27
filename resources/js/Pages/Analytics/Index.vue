<template>
    <AppLayout>
        <template #breadcrumb>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $t('nav.analytics') }}</span>
        </template>

        <PageHeader :title="$t('nav.analytics')" description="">
            <template #actions>
                <div class="flex items-center space-x-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-md">
                    <button v-for="p in periods" :key="p.value" @click="setPeriod(p.value)"
                            class="px-3 py-1.5 text-xs font-medium rounded-sm transition-colors"
                            :class="activePeriod === p.value ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'">
                        {{ p.label }}
                    </button>
                </div>
            </template>
        </PageHeader>

        <!-- Revenue vs Costs Line Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <Card class="p-6 lg:col-span-2">
                <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                    <TrendingUpIcon class="w-5 h-5 text-orange-500" />
                    Revenue vs Costs (Monthly)
                </h3>
                <div v-if="revenueVsCosts.length" class="h-64">
                    <Line :data="revenueChartData" :options="lineOptions" />
                </div>
                <div v-else class="h-64 flex flex-col items-center justify-center text-slate-400">
                    <BarChart2Icon class="w-10 h-10 mb-2 opacity-30" />
                    <span class="text-sm">No data for this period</span>
                </div>
            </Card>

            <!-- Material Breakdown Doughnut -->
            <Card class="p-6">
                <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                    <PieChartIcon class="w-5 h-5 text-blue-500" />
                    Material Breakdown
                </h3>
                <div v-if="materialBreakdown.length" class="h-64 flex items-center justify-center">
                    <Doughnut :data="materialChartData" :options="doughnutOptions" />
                </div>
                <div v-else class="h-64 flex flex-col items-center justify-center text-slate-400">
                    <PieChartIcon class="w-10 h-10 mb-2 opacity-30" />
                    <span class="text-sm">No data</span>
                </div>
            </Card>
        </div>

        <!-- Monthly Order Count Bar Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card class="p-6">
                <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                    <BarChart2Icon class="w-5 h-5 text-indigo-500" />
                    Monthly Orders
                </h3>
                <div v-if="monthlyOrders.length" class="h-52">
                    <Bar :data="monthlyOrdersChartData" :options="barOptions" />
                </div>
                <div v-else class="h-52 flex items-center justify-center text-slate-400 text-sm">No data</div>
            </Card>

            <!-- Top Customers Table -->
            <Card class="p-0 overflow-hidden">
                <div class="p-5 border-b bg-slate-50 dark:bg-slate-900/50 flex items-center gap-2">
                    <UsersIcon class="w-4 h-4 text-slate-500" />
                    <h3 class="font-semibold text-base">Top Customers</h3>
                </div>
                <div class="overflow-y-auto max-h-60">
                    <table class="w-full text-sm" v-if="topCustomers.length">
                        <thead class="bg-slate-50 dark:bg-slate-900 border-b">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Orders</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(c, i) in topCustomers" :key="i" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 text-xs font-bold">
                                            {{ i + 1 }}
                                        </div>
                                        <span class="font-medium">{{ c.customer_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-500">{{ c.total_orders }}</td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-orange-600">{{ parseFloat(c.total_spent).toFixed(2) }} {{ $t('common.currency') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="px-4 py-10 text-center text-slate-400 text-sm">No delivered orders yet</div>
                </div>
            </Card>
        </div>

        <!-- Product Profitability Table -->
        <Card class="p-0 overflow-hidden">
            <div class="p-5 border-b bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <PackageIcon class="w-4 h-4 text-slate-500" />
                    <h3 class="font-semibold text-base">Product Profitability</h3>
                </div>
                <span class="text-xs text-slate-400">Delivered orders only</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" v-if="productProfitability.length">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Units Sold</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Revenue</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Est. Cost</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Gross Profit</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Margin</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Filament Used</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="p in productProfitability" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: p.color_hex || '#94a3b8' }"></div>
                                    <span class="font-medium">{{ p.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600">{{ p.units_sold }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold">{{ parseFloat(p.revenue).toFixed(2) }} {{ $t('common.currency') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-slate-500">{{ (parseFloat(p.print_costs) + parseFloat(p.filament_cost)).toFixed(2) }} {{ $t('common.currency') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold" :class="p.gross_profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600'">
                                {{ parseFloat(p.gross_profit).toFixed(2) }} {{ $t('common.currency') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                      :class="p.margin >= 30 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : p.margin >= 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'">
                                    {{ p.margin }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-500 font-mono">{{ (parseFloat(p.total_grams) / 1000).toFixed(2) }}kg</td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="px-4 py-12 text-center text-slate-400 text-sm">No delivered orders yet</div>
            </div>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Card } from '@/Components/ui/card';
import { TrendingUpIcon, BarChart2Icon, PieChartIcon, UsersIcon, PackageIcon } from 'lucide-vue-next';
import {
    Chart as ChartJS,
    ArcElement, Tooltip, Legend,
    CategoryScale, LinearScale, PointElement, LineElement, BarElement,
    Filler
} from 'chart.js';
import { Doughnut, Line, Bar } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Filler);

const props = defineProps({
    revenueVsCosts:       { type: Array, default: () => [] },
    topCustomers:         { type: Array, default: () => [] },
    materialBreakdown:    { type: Array, default: () => [] },
    productProfitability: { type: Array, default: () => [] },
    monthlyOrders:        { type: Array, default: () => [] },
    filters:              { type: Object, required: true },
});

const { t } = useI18n();

const periods = [
    { label: 'This Month',  value: 'this_month' },
    { label: 'Last Month',  value: 'last_month' },
    { label: 'This Year',   value: 'this_year' },
    { label: 'Last Year',   value: 'last_year' },
    { label: 'All Time',    value: 'all_time' },
];

const activePeriod = ref(props.filters.period || 'this_year');
const setPeriod = (val) => {
    activePeriod.value = val;
    router.get(route('analytics.index'), { period: val }, { preserveState: true, replace: true });
};

// ── Revenue vs Costs Line Chart ─────────────────────────────────────────────
const revenueChartData = computed(() => ({
    labels: props.revenueVsCosts.map(d => d.month),
    datasets: [
        {
            label: 'Revenue',
            data: props.revenueVsCosts.map(d => d.revenue),
            borderColor: '#f97316',
            backgroundColor: 'rgba(249,115,22,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
        },
        {
            label: 'Costs',
            data: props.revenueVsCosts.map(d => d.costs),
            borderColor: '#64748b',
            backgroundColor: 'rgba(100,116,139,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
        },
        {
            label: 'Profit',
            data: props.revenueVsCosts.map(d => d.profit),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.08)',
            tension: 0.4,
            borderDash: [4, 4],
            fill: false,
            pointRadius: 3,
        },
    ],
}));

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
    scales: {
        y: { grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
    },
};

// ── Material Breakdown Doughnut ─────────────────────────────────────────────
const materialChartData = computed(() => ({
    labels: props.materialBreakdown.map(d => (d.material || 'Unknown').toUpperCase()),
    datasets: [{
        data: props.materialBreakdown.map(d => d.revenue),
        backgroundColor: ['#f97316', '#3b82f6', '#10b981', '#8b5cf6', '#64748b'],
    }],
}));

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
};

// ── Monthly Orders Bar Chart ────────────────────────────────────────────────
const monthlyOrdersChartData = computed(() => ({
    labels: props.monthlyOrders.map(d => d.month),
    datasets: [
        {
            label: 'Total',
            data: props.monthlyOrders.map(d => d.total),
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderRadius: 4,
        },
        {
            label: 'Delivered',
            data: props.monthlyOrders.map(d => d.delivered),
            backgroundColor: 'rgba(16,185,129,0.7)',
            borderRadius: 4,
        },
    ],
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
    scales: {
        y: { grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { stepSize: 1, font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
    },
};
</script>
