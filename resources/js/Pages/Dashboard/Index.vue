<template>
    <AppLayout>
        <template #breadcrumb>
            <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $t('nav.dashboard') }}</span>
        </template>
        
        <PageHeader :title="$t('dashboard.title')" :description="$t('dashboard.desc')">
            <template #actions>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-md">
                        <button v-for="p in periods" :key="p.value" @click="setPeriod(p.value)"
                                class="px-3 py-1.5 text-xs font-medium rounded-sm transition-colors"
                                :class="activePeriod === p.value ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'">
                            {{ p.label }}
                        </button>
                    </div>
                    <select v-model="activePeriod" @change="setPeriod($event.target.value)" class="h-8 text-xs font-medium rounded-md border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 focus:ring-orange-500 focus:border-orange-500">
                        <option value="" disabled>{{ $t('dashboard.select_month') }}</option>
                        <option v-for="m in pastMonths" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>
            </template>
        </PageHeader>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <Card v-for="(kpi, i) in kpiConfig" :key="i" class="p-5 flex flex-col justify-between" :class="kpi.containerClass">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 tracking-wide uppercase">{{ kpi.label }}</p>
                        <h2 class="text-3xl font-mono font-bold mt-2" :class="kpi.textClass">{{ kpi.value }}</h2>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                        <component :is="kpi.icon" class="w-6 h-6" :class="kpi.iconClass" />
                    </div>
                </div>
            </Card>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card class="p-6">
                <h3 class="font-semibold text-lg mb-4">{{ $t('dashboard.cost_breakdown') }}</h3>
                <div v-if="stats.costsByCategory.length" class="h-64 flex justify-center">
                    <Doughnut :data="donutData" :options="donutOptions" />
                </div>
                <div v-else class="h-64 flex items-center justify-center text-slate-400">{{ $t('dashboard.no_cost_data') }}</div>
            </Card>

            <Card class="p-6">
                <h3 class="font-semibold text-lg mb-4">{{ $t('dashboard.top_revenue') }}</h3>
                <!-- Simple bar representation -->
                <div class="space-y-4" v-if="stats.revenueByProduct.length">
                    <div v-for="prod in stats.revenueByProduct" :key="prod.name" class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full shadow-inner" :style="{ backgroundColor: prod.color_hex }"></div>
                            <span class="text-sm font-medium">{{ prod.name }}</span>
                        </div>
                        <span class="text-sm font-mono font-bold">{{ parseFloat(prod.revenue).toFixed(2) }} {{ $t('common.currency') }}</span>
                    </div>
                </div>
                <div v-else class="h-64 flex items-center justify-center text-slate-400">{{ $t('dashboard.no_rev_data') }}</div>
            </Card>
        </div>

        <!-- Operational Stats -->
        <h3 class="font-semibold text-lg mb-4 mt-8">{{ $t('dashboard.operations') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <Card v-for="(op, i) in opStats" :key="i" class="p-4 text-center">
                <p class="text-xs text-slate-500 mb-1 line-clamp-1" :title="op.label">{{ op.label }}</p>
                <p class="text-xl font-bold font-mono">{{ op.value }}</p>
            </Card>
        </div>

        <!-- Bottom Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card class="p-0 overflow-hidden lg:col-span-2">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-lg">{{ $t('dashboard.recent_orders') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900 border-b">
                            <tr>
                                <th class="px-4 py-3 font-semibold">{{ $t('dashboard.table.order') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('dashboard.table.customer') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('dashboard.table.status') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('dashboard.table.time') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ $t('dashboard.table.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in stats.recentOrders" :key="order.id" class="border-b last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 font-mono text-orange-600">{{ order.order_number }}</td>
                                <td class="px-4 py-3 font-medium">{{ order.customer_name }}</td>
                                <td class="px-4 py-3"><StatusBadge :status="order.status" /></td>
                                <td class="px-4 py-3 text-slate-500">{{ formattedPrintTime(order.estimated_print_minutes) }}</td>
                                <td class="px-4 py-3 font-mono font-bold">{{ order.total_price }} {{ $t('common.currency') }}</td>
                            </tr>
                            <tr v-if="!stats.recentOrders.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ $t('dashboard.no_orders') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
            
            <Card class="p-6">
                <h3 class="font-semibold text-lg mb-4">{{ $t('dashboard.orders_by_status') }}</h3>
                <div class="space-y-4">
                    <div v-for="(count, status) in stats.ordersByStatus" :key="status" class="flex items-center justify-between p-3 rounded-lg border">
                        <StatusBadge :status="status" />
                        <span class="font-mono font-bold text-lg">{{ count }}</span>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Card } from '@/Components/ui/card';
import { DollarSignIcon, TrendingDownIcon, WalletIcon, PercentIcon, BoxIcon, TicketIcon } from 'lucide-vue-next';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    stats: { type: Object, required: true },
    filters: { type: Object, required: true }
});

const { t, locale } = useI18n();

const periods = computed(() => [
    { label: t('dashboard.periods.this_month'), value: 'this_month' },
    { label: t('dashboard.periods.last_month'), value: 'last_month' },
    { label: t('dashboard.periods.this_year'), value: 'this_year' },
    { label: t('dashboard.periods.last_year'), value: 'last_year' },
    { label: t('dashboard.periods.all_time'), value: 'all_time' },
]);

const activePeriod = ref(localStorage.getItem('dashboard_period') || props.filters.period || 'this_month');

const pastMonths = computed(() => {
    const months = [];
    const date = new Date();
    for (let i = 0; i < 12; i++) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const value = `${year}-${month}`;
        const label = new Intl.DateTimeFormat(locale.value || 'en', { month: 'long', year: 'numeric' }).format(date);
        months.push({ value, label });
        date.setMonth(date.getMonth() - 1);
    }
    return months;
});

const setPeriod = (val) => {
    activePeriod.value = val;
    localStorage.setItem('dashboard_period', val);
    router.get(route('dashboard'), { period: val }, { preserveState: true, replace: true });
};

const formattedPrintTime = (minutes) => {
    if (!minutes && minutes !== 0) return '—';
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    if (h > 0 && m > 0) return `${h}h ${m}m`;
    if (h > 0) return `${h}h`;
    return `${m}m`;
};

// KPIs mapped from stats
const kpiConfig = computed(() => {
    const s = props.stats.kpis;
    const isProfit = s.grossProfit >= 0;
    return [
        { label: t('dashboard.kpis.revenue'), value: `${parseFloat(s.totalRevenue).toFixed(2)} ${t('common.currency')}`, icon: DollarSignIcon, iconClass: 'text-orange-500' },
        { label: t('dashboard.kpis.costs'), value: `${parseFloat(s.totalCosts).toFixed(2)} ${t('common.currency')}`, icon: TrendingDownIcon, iconClass: 'text-slate-500' },
        { 
            label: t('dashboard.kpis.profit'), value: `${parseFloat(s.grossProfit).toFixed(2)} ${t('common.currency')}`, 
            icon: WalletIcon, 
            iconClass: isProfit ? 'text-green-600' : 'text-red-500',
            containerClass: isProfit ? 'border-green-200 dark:border-green-900/50 bg-green-50/50 dark:bg-green-900/10' : 'border-red-200 dark:border-red-900/50 bg-red-50/50 dark:bg-red-900/10',
            textClass: isProfit ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'
        },
        { label: t('dashboard.kpis.total_orders'), value: s.totalOrders, icon: BoxIcon, iconClass: 'text-indigo-500' },
        { label: t('dashboard.kpis.active_orders'), value: s.activeOrders, icon: BoxIcon, iconClass: 'text-blue-500' },
        { label: t('dashboard.kpis.avg_order'), value: `${s.avgOrderValue} ${t('common.currency')}`, icon: TicketIcon, iconClass: 'text-teal-500' },
    ];
});

// Op Stats
const opStats = computed(() => [
    { label: t('dashboard.op_stats.catalog'), value: props.stats.operationalStats.totalProducts },
    { label: t('dashboard.op_stats.avg_price'), value: `${props.stats.operationalStats.avgProductPrice} ${t('common.currency')}` },
    { label: t('dashboard.op_stats.deliveries'), value: props.stats.operationalStats.totalPrints },
    { label: t('dashboard.op_stats.print_hours'), value: `${props.stats.operationalStats.totalPrintHours}h` },
    { label: t('dashboard.op_stats.filament'), value: `${props.stats.operationalStats.totalFilamentKg}kg` },
    { label: t('dashboard.op_stats.queue'), value: formattedPrintTime(props.stats.operationalStats.queueMinutes) },
]);

// Doughnut Chart Mapping
const donutData = computed(() => {
    const dt = props.stats.costsByCategory;
    return {
        labels: dt.map(i => i.name),
        datasets: [{
            data: dt.map(i => i.total),
            backgroundColor: ['#F97316', '#3b82f6', '#10b981', '#6366f1', '#8b5cf6', '#64748b']
        }]
    };
});

const donutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right' }
    }
};
</script>
