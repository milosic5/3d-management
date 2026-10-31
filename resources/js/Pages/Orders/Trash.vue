<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('orders.index')" class="hover:underline text-slate-500">{{ $t('orders.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('orders.trash') }}</span>
        </div>
    </template>

    <PageHeader :title="$t('orders.trash_title')" :description="$t('orders.trash_description')">
      <template #actions>
        <Link :href="route('orders.index')">
            <Button variant="outline"><ClipboardListIcon class="w-4 h-4 mr-2" /> {{ $t('orders.back_to_orders') }}</Button>
        </Link>
      </template>
    </PageHeader>

    <DataTable :data="orders.data" :columns="columns" :page-size="15" :pagination="orders">
        <template #empty>
            <div class="flex flex-col items-center justify-center py-8 text-slate-500">
                <TrashIcon class="w-12 h-12 mb-3 opacity-20" />
                <p>{{ $t('orders.trash_empty') }}</p>
            </div>
        </template>
    </DataTable>

    <ConfirmDialog
        v-model:is-open="isForceDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('orders.force_delete_confirm')"
        @confirm="forceDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref, h } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { RefreshCcwIcon, ClipboardListIcon, TrashIcon } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

defineProps({ orders: Object })
const { t } = useI18n()

const isForceDeleteDialogOpen = ref(false)
const orderToDelete = ref(null)

const restore = (id) => {
    router.post(route('orders.restore', id), {}, { preserveScroll: true })
}

const confirmForceDelete = (order) => {
    orderToDelete.value = order
    isForceDeleteDialogOpen.value = true
}

const forceDelete = () => {
    if (orderToDelete.value) {
        router.delete(route('orders.forceDelete', orderToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                isForceDeleteDialogOpen.value = false
                orderToDelete.value = null
            },
        })
    }
}

const columns = [
  { header: t('orders.order_number'), accessorKey: 'order_number' },
  { header: t('orders.customer_name'), accessorKey: 'customer_name' },
  { header: t('orders.status'), accessorKey: 'status', cell: ({ row }) => t('status.' + row.original.status) },
  { header: t('orders.items_count'), accessorKey: 'items_count' },
  { header: t('orders.total_price'), accessorKey: 'total_price' },
  {
    header: t('orders.deleted_at'),
    cell: ({ row }) => new Date(row.original.deleted_at).toLocaleString()
  },
  {
    id: 'actions',
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
        h(Button, { variant: 'outline', size: 'sm', class: 'text-green-600', onClick: () => restore(row.original.id) }, () => [h(RefreshCcwIcon, { class: 'w-4 h-4 mr-1' }), t('orders.restore')]),
        h(Button, { variant: 'outline', size: 'sm', class: 'text-red-500 hover:text-red-600', onClick: () => confirmForceDelete(row.original) }, () => [h(TrashIcon, { class: 'w-4 h-4 mr-1' }), t('orders.force_delete')])
    ])
  }
]
</script>
