<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('orders.title') }}</span></template>
    
    <PageHeader :title="$t('orders.title')" description="">
      <template #actions>
        <a :href="excelExportUrl" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors mr-2">
            <DownloadIcon class="w-4 h-4 mr-1.5 text-green-600" /> Excel
        </a>
        <Link :href="route('orders.create')">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('orders.new') }}</Button>
        </Link>
      </template>
    </PageHeader>
    
    <DataTable :data="orders.data" :columns="columns" :page-size="15" searchable :search-placeholder="$t('orders.search_placeholder')" v-model="form.search" :pagination="orders" :server-sort="{ key: form.sort, dir: form.direction }" @sort="onSort">
        <template #filters>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Period filter pills -->
                <div class="inline-flex items-center bg-slate-100 dark:bg-slate-800 p-0.5 rounded-md text-xs">
                    <button
                        v-for="p in periodOptions"
                        :key="p.value"
                        type="button"
                        @click="setPeriod(p.value)"
                        class="px-2.5 py-1.5 font-medium rounded transition-colors whitespace-nowrap"
                        :class="(form.period || '') === p.value ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    >
                        {{ p.label }}
                    </button>
                </div>

                <!-- Product filter -->
                <div class="w-56">
                    <SearchableSelect
                        v-model="form.product_id"
                        :options="productOptions"
                        :placeholder="$t('orders.filter_product')"
                        :search-placeholder="$t('common.search')"
                    />
                </div>

                <!-- Status filter -->
                <select v-model="form.status" class="h-10 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="">{{ $t('common.all_statuses') }}</option>
                    <option value="received">{{ $t('status.received') }}</option>
                    <option value="printing">{{ $t('status.printing') }}</option>
                    <option value="finished">{{ $t('status.finished') }}</option>
                    <option value="delivered">{{ $t('status.delivered') }}</option>
                </select>

                <!-- Show cancelled checkbox -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="show_cancelled" v-model="form.show_cancelled" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                    <label for="show_cancelled" class="text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $t('orders.show_cancelled') }}</label>
                </div>

                <!-- Reset filters button -->
                <Button
                    v-if="hasActiveFilters"
                    variant="ghost"
                    size="sm"
                    type="button"
                    @click="resetFilters"
                    class="h-9 px-2.5 text-xs text-slate-500 hover:text-red-600 dark:hover:text-red-400"
                >
                    <RotateCcwIcon class="w-3.5 h-3.5 mr-1.5" />
                    {{ $t('orders.reset_filters') }}
                </Button>
            </div>
        </template>
    </DataTable>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('orders.delete_confirm')"
        @confirm="deleteOrder"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import { Button } from '@/Components/ui/button'
import { PlusIcon, EyeIcon, PencilIcon, TrashIcon, DownloadIcon, CopyIcon, RotateCcwIcon } from 'lucide-vue-next'
import { useFilters } from '@/composables/useFilters'
import { useI18n } from 'vue-i18n'

const props = defineProps({ orders: Object, filters: Object, products: { type: Array, default: () => [] } })
const { form } = useFilters(props.filters)
const { t } = useI18n()

const periodOptions = computed(() => [
    { value: '', label: t('orders.period_all') },
    { value: 'today', label: t('orders.period_today') },
    { value: 'this_month', label: t('orders.period_this_month') },
    { value: 'last_30_days', label: t('orders.period_last_30_days') }
])

const setPeriod = (val) => {
    form.value.period = val
}

const productOptions = computed(() => [
    { value: '', label: t('orders.all_products') },
    ...(props.products || []).map(p => ({
        value: p.id,
        label: p.name
    }))
])

const hasActiveFilters = computed(() => {
    return Boolean(
        form.value.search ||
        form.value.status ||
        form.value.product_id ||
        form.value.period ||
        form.value.show_cancelled
    )
})

const resetFilters = () => {
    form.value.search = ''
    form.value.status = ''
    form.value.product_id = ''
    form.value.period = ''
    form.value.show_cancelled = false
}

const excelExportUrl = computed(() => {
    const params = {}
    if (form.value.status) params.status = form.value.status
    if (form.value.period) params.period = form.value.period
    if (form.value.product_id) params.product_id = form.value.product_id
    if (form.value.show_cancelled) params.show_cancelled = 1
    return route('export.orders.excel', params)
})

const onSort = ({ key, dir }) => {
    form.value.sort = key;
    form.value.direction = dir;
}

const isDeleteDialogOpen = ref(false)
const orderToDelete = ref(null)

const confirmDelete = (order) => {
    orderToDelete.value = order
    isDeleteDialogOpen.value = true
}

const deleteOrder = () => {
    if (orderToDelete.value) {
        router.delete(route('orders.destroy', orderToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => isDeleteDialogOpen.value = false
        })
    }
}

const updateStatus = (orderId, newStatus) => {
    router.patch(route('orders.update', orderId), { status: newStatus }, {
        preserveScroll: true,
        preserveState: true
    })
}

const columns = [
  {
    header: t('orders.order_number'),
    accessorKey: 'order_number',
    cell: ({ row }) => h(Link, { href: route('orders.show', row.original.id), class: 'hover:underline text-orange-600' }, () => h('span', { class: 'font-mono font-medium' }, row.original.order_number))
  },
  {
    header: t('orders.order_items'),
    cell: ({ row }) => {
        const items = row.original.items || []
        if (items.length === 0) return null
        return h(Link, { href: route('orders.show', row.original.id) }, () => h('div', { class: 'flex flex-col gap-1 cursor-pointer hover:opacity-80' }, items.map(item => {
            return h('span', { class: 'text-xs bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border dark:border-slate-700 w-fit' }, 
              `${item.quantity}x ${item.product?.name || t('orders.product')}`
            )
        })))
    }
  },
  { header: t('orders.customer_name'), accessorKey: 'customer_name' },
  {
    header: t('orders.status'),
    cell: ({ row }) => {
        const bgColors = {
            'received': 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            'printing': 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            'finished': 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200',
            'delivered': 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'
        }
        
        return h('select', {
            class: `h-8 text-[10px] uppercase tracking-wider font-bold rounded-full border border-transparent px-4 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 cursor-pointer appearance-none text-center min-w-[100px] ${bgColors[row.original.status] || ''}`,
            value: row.original.status,
            onChange: (e) => updateStatus(row.original.id, e.target.value)
        }, [
            h('option', { value: 'received', class: 'bg-white text-slate-900 dark:bg-slate-900 dark:text-white' }, t('status.received')),
            h('option', { value: 'printing', class: 'bg-white text-slate-900 dark:bg-slate-900 dark:text-white' }, t('status.printing')),
            h('option', { value: 'finished', class: 'bg-white text-slate-900 dark:bg-slate-900 dark:text-white' }, t('status.finished')),
            h('option', { value: 'delivered', class: 'bg-white text-slate-900 dark:bg-slate-900 dark:text-white' }, t('status.delivered')),
            h('option', { value: 'cancelled', class: 'bg-white text-slate-900 dark:bg-slate-900 dark:text-white' }, t('status.cancelled'))
        ])
    }
  },
  {
    header: t('orders.items_count'),
    cell: ({ row }) => h('span', { class: 'text-slate-500 font-medium' }, row.original.items?.length || 0)
  },
  {
    header: t('orders.total_price'),
    accessorKey: 'total_price',
    cell: ({ row }) => h('span', { class: 'font-mono font-bold' }, `${row.original.total_price} ${t('common.currency')}`)
  },
  {
    header: t('orders.filament_used'),
    cell: ({ row }) => {
        const items = row.original.items || []
        const totalGrams = items.reduce((sum, item) => sum + (Number(item.weight_grams) || 0), 0)
        return h('span', { class: 'text-slate-500 font-medium' }, `${totalGrams}g`)
    }
  },
  {
    header: t('orders.created_at'),
    accessorKey: 'created_at',
    cell: ({ row }) => new Date(row.original.created_at).toLocaleDateString()
  },
  {
    id: 'actions',
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-1' }, [
        h(Link, { href: route('orders.show', row.original.id) }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(EyeIcon, { class: 'w-4 h-4' }))),
        h(Link, { href: route('orders.edit', row.original.id) }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(PencilIcon, { class: 'w-4 h-4 text-slate-500' }))),
        h(Link, { href: route('orders.create', { duplicate_from: row.original.id }), title: 'Duplicate' }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(CopyIcon, { class: 'w-4 h-4 text-slate-500' }))),
        h('a', { href: route('export.order.pdf', row.original.id), title: 'Download PDF', class: 'inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }, h(DownloadIcon, { class: 'w-4 h-4 text-slate-400 hover:text-red-500' })),
        h(Button, { variant: 'ghost', size: 'icon', onClick: () => confirmDelete(row.original) }, () => h(TrashIcon, { class: 'w-4 h-4 text-red-500' }))
    ])
  }
]
</script>