<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('orders.index')" class="hover:underline text-slate-500">{{ $t('orders.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium font-mono">{{ order.order_number }}</span>
        </div>
    </template>
    
    <PageHeader :title="'Order ' + order.order_number" description="">
      <template #actions>
        <span class="text-sm font-medium mr-3 text-slate-500">{{ $t('orders.change_status') }}</span>
        <select v-model="status" @change="updateStatus" class="h-9 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-orange-500 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-orange-500">
            <option value="received">{{ $t('status.received') }}</option>
            <option value="printing">{{ $t('status.printing') }}</option>
            <option value="finished">{{ $t('status.finished') }}</option>
            <option value="delivered">{{ $t('status.delivered') }}</option>
            <option value="cancelled">{{ $t('status.cancelled') }}</option>
        </select>
        <StatusBadge :status="order.status" class="ml-4" />
        <Link :href="route('orders.edit', order.id)" class="ml-3">
            <Button variant="outline" size="sm" class="dark:border-slate-700 dark:text-slate-200">
                <PencilIcon class="w-4 h-4 mr-1" /> {{ $t('common.edit') }}
            </Button>
        </Link>
        <a :href="route('export.order.pdf', order.id)" class="ml-2 inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors">
            <FileTextIcon class="w-4 h-4 mr-1.5" /> PDF
        </a>
      </template>
    </PageHeader>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Lines -->
        <div class="lg:col-span-2 space-y-6">
            <Card class="p-0 overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-semibold">{{ $t('orders.order_items') }}</h3>
                    <span class="text-sm text-slate-500 font-medium">{{ order.items.length }} {{ $t('orders.items_count').toLowerCase() }}</span>
                </div>
                <div class="divide-y">
                    <div v-for="item in order.items" :key="item.id" class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div v-if="item.product.image_path" class="flex-shrink-0">
                                    <img :src="`/storage/${item.product.image_path}`" class="w-16 h-16 object-cover rounded shadow-sm border" />
                                </div>
                                <div v-else class="flex-shrink-0 w-16 h-16 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center border">
                                    <BoxIcon class="w-6 h-6 text-slate-400" />
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-lg">{{ item.product.name }}</h4>
                                    <div class="flex items-center space-x-3 text-sm mt-1">
                                        <MaterialBadge :material="item.product.material" />
                                        <ColorSwatch :colorHex="item.color_hex" :colorName="item.color_name" />
                                        <span class="text-slate-500">{{ item.weight_grams }}{{ $t('orders.weight_per_unit') }}</span>
                                    </div>
                                    <p v-if="item.notes" class="text-xs text-orange-600 font-medium mt-2 bg-orange-50 dark:bg-orange-900/20 px-2 py-1 rounded w-fit">
                                        {{ $t('orders.note_prefix') }} {{ item.notes }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-lg font-mono font-bold">{{ parseFloat(item.unit_price).toFixed(2) }} {{ $t('common.currency') }}</div>
                                <div class="text-sm font-medium text-slate-500 mt-1">{{ $t('orders.qty') }}: {{ item.quantity }}</div>
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-200 border-t pt-1 mt-1">
                                    {{ (item.unit_price * item.quantity).toFixed(2) }} {{ $t('common.currency') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Order History Timeline -->
            <Card class="p-0 overflow-hidden" v-if="order.history && order.history.length">
                <div class="p-5 border-b bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-2">
                    <ClockIcon class="w-4 h-4 text-slate-500" />
                    <h3 class="text-base font-semibold">{{ $t('orders.history_title') || 'Order History' }}</h3>
                </div>
                <div class="p-5">
                    <ol class="relative border-l border-slate-200 dark:border-slate-700 space-y-6 ml-3">
                        <li v-for="entry in order.history" :key="entry.id" class="ml-6">
                            <!-- Timeline dot -->
                            <span class="absolute -left-[9px] flex items-center justify-center w-4 h-4 rounded-full ring-2 ring-white dark:ring-slate-950"
                                  :class="historyDotClass(entry)">
                            </span>

                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                        {{ historyActionLabel(entry) }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $t('orders.history_by') || 'by' }} <span class="font-medium">{{ entry.user?.name || '—' }}</span>
                                    </p>
                                </div>
                                <time class="text-xs text-slate-400 whitespace-nowrap ml-4">
                                    {{ formatHistoryDate(entry.created_at) }}
                                </time>
                            </div>
                        </li>
                    </ol>
                </div>
            </Card>
        </div>

        <!-- Sidebar Summary -->
        <div class="lg:col-span-1 space-y-6">
            <Card class="p-6">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ $t('orders.customer_and_details') }}</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">{{ $t('orders.customer_name') }}</p>
                        <p class="font-medium text-slate-800 dark:text-slate-200 text-lg">{{ order.customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">{{ $t('orders.creation_date') }}</p>
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ new Date(order.created_at).toLocaleString() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">{{ $t('orders.processing_operator') }}</p>
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ order.creator.name }}</p>
                    </div>
                    <div v-if="order.notes">
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">{{ $t('orders.order_notes') }}</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 italic p-3 bg-slate-50 dark:bg-slate-900 rounded-md border">{{ order.notes }}</p>
                    </div>
                </div>
            </Card>

            <Card class="p-6 bg-slate-50 dark:bg-slate-900/50 border-orange-200 dark:border-orange-900/50">
                <h3 class="text-lg font-semibold mb-4 text-orange-600 dark:text-orange-500">{{ $t('orders.totals_summary') }}</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-600 dark:text-slate-400">{{ $t('orders.total_items') }}</span>
                        <span class="font-bold">{{ totalItemsCount }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-600 dark:text-slate-400">{{ $t('orders.est_total_print_time') }}</span>
                        <span class="font-medium">{{ formattedTime }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm border-b pb-3">
                        <span class="font-medium text-slate-600 dark:text-slate-400">{{ $t('orders.total_filament_used') }}</span>
                        <span class="font-medium">{{ totalWeight }}g ({{ (totalWeight / 1000).toFixed(2) }}kg)</span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-lg font-bold">{{ $t('orders.total_quote') }}</span>
                        <span class="text-2xl font-mono font-bold text-orange-600 dark:text-orange-500">{{ parseFloat(order.total_price).toFixed(2) }} {{ $t('common.currency') }}</span>
                    </div>
                </div>
            </Card>
        </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import MaterialBadge from '@/Components/MaterialBadge.vue'
import ColorSwatch from '@/Components/ColorSwatch.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { BoxIcon, PencilIcon, ClockIcon, FileTextIcon } from 'lucide-vue-next'
import { usePrintTime } from '@/composables/usePrintTime'
import { toast } from 'vue-sonner'
import { useI18n } from 'vue-i18n'

const props = defineProps({ order: Object })
const { t } = useI18n()

const status = ref(props.order.status)

const updateStatus = () => {
    router.put(route('orders.update', props.order.id), { status: status.value }, {
        preserveScroll: true,
        onSuccess: () => toast.success(t('status.' + status.value)),
    })
}

const totalItemsCount = computed(() => {
    return props.order.items.reduce((acc, curr) => acc + curr.quantity, 0)
})

const totalWeight = computed(() => {
    return props.order.items.reduce((acc, curr) => acc + Number(curr.weight_grams || 0), 0)
})

const printMins = ref(props.order.estimated_print_minutes)
const { formattedPrintTime: formattedTime } = usePrintTime(printMins)

// ── History helpers ───────────────────────────────────────────────────────────

const historyDotClass = (entry) => {
    const map = {
        created:        'bg-green-500',
        status_changed: 'bg-blue-500',
        updated:        'bg-orange-400',
    }
    return map[entry.action] || 'bg-slate-400'
}

const historyActionLabel = (entry) => {
    if (entry.action === 'created') {
        return t('orders.history_created') || `Order created (${entry.to_status})`
    }
    if (entry.action === 'status_changed') {
        const from = entry.from_status ? t('status.' + entry.from_status) : '—'
        const to   = entry.to_status   ? t('status.' + entry.to_status)   : '—'
        return `${from} → ${to}`
    }
    if (entry.action === 'updated') {
        return t('orders.history_updated') || 'Order details updated'
    }
    return entry.action
}

const formatHistoryDate = (dateStr) => {
    return new Date(dateStr).toLocaleString()
}
</script>
