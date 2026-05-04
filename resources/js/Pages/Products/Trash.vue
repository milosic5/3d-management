<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('products.index')" class="hover:underline text-slate-500">Products</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">Trash</span>
        </div>
    </template>
    
    <PageHeader title="Deleted Products" description="Restore or permanently delete removed catalog items.">
      <template #actions>
        <Link :href="route('products.index')">
            <Button variant="outline"><BoxIcon class="w-4 h-4 mr-2" /> Back to Active</Button>
        </Link>
      </template>
    </PageHeader>
    
    <DataTable :data="products.data" :columns="columns" :page-size="15">
        <template #empty>
            <div class="flex flex-col items-center justify-center py-8 text-slate-500">
                <TrashIcon class="w-12 h-12 mb-3 opacity-20" />
                <p>The trash bin is empty.</p>
            </div>
        </template>
    </DataTable>
  </AppLayout>
</template>

<script setup>
import { h } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import { Button } from '@/Components/ui/button'
import { RefreshCcwIcon, BoxIcon, TrashIcon } from 'lucide-vue-next'

const props = defineProps({ products: Object })

const restore = (id) => {
    router.post(route('products.restore', id), {}, { preserveScroll: true })
}

const forceDelete = (id) => {
    if (confirm('Permanently delete this product? Files will be lost.')) {
        router.delete(route('products.forceDelete', id), { preserveScroll: true })
    }
}

const columns = [
  { header: 'Product Name', accessorKey: 'name' },
  { header: 'Material', accessorKey: 'material', cell: ({ row }) => row.original.material.toUpperCase() },
  {
    header: 'Deleted At',
    cell: ({ row }) => new Date(row.original.deleted_at).toLocaleString()
  },
  {
    id: 'actions',
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
        h(Button, { variant: 'outline', size: 'sm', class: 'text-green-600', onClick: () => restore(row.original.id) }, () => [h(RefreshCcwIcon, { class: 'w-4 h-4 mr-1' }), 'Restore']),
        h(Button, { variant: 'outline', size: 'sm', class: 'text-red-500 hover:text-red-600', onClick: () => forceDelete(row.original.id) }, () => [h(TrashIcon, { class: 'w-4 h-4 mr-1' }), 'Delete Perm.'])
    ])
  }
]
</script>
