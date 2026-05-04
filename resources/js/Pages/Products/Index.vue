<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('products.title') }}</span></template>
    
    <PageHeader :title="$t('products.title')" description="">
      <template #actions>
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" class="mr-2">{{ $t('table.columns_toggle') }}</Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
                <DropdownMenuCheckboxItem
                    v-for="col in columns.filter(c => c.id !== 'actions')"
                    :key="col.id || col.accessorKey || col.header"
                    :checked="columnVisibility[col.id || col.accessorKey || col.header]"
                    @update:checked="(val) => setColumnVisibility({ ...columnVisibility, [col.id || col.accessorKey || col.header]: val })"
                >
                    {{ col.header || col.id || col.accessorKey }}
                </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <Link :href="route('products.trash')">
            <Button variant="outline" class="mr-2"><TrashIcon class="w-4 h-4 mr-2" /> {{ $t('products.trash') }}</Button>
        </Link>
        <Link :href="route('products.create')">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('products.new') }}</Button>
        </Link>
      </template>
    </PageHeader>
    
    <DataTable 
        :data="products.data" 
        :columns="columns" 
        :page-size="15" 
        searchable 
        :search-placeholder="$t('products.search_placeholder')" 
        v-model="form.search"
        :column-visibility="columnVisibility"
        @update:column-visibility="setColumnVisibility"
        :pagination="products"
        :server-sort="{ key: form.sort, dir: form.direction }"
        @sort="onSort"
    >
    </DataTable>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('products.delete_confirm')"
        @confirm="deleteProduct"
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
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuCheckboxItem } from '@/Components/ui/dropdown-menu'
import { PlusIcon, PencilIcon, TrashIcon, BoxIcon } from 'lucide-vue-next'
import { useFilters } from '@/composables/useFilters'
import { usePrintTime } from '@/composables/usePrintTime'
import { useColumnVisibility } from '@/composables/useColumnVisibility'
import { useI18n } from 'vue-i18n'

const props = defineProps({ products: Object, filters: Object })
const { form } = useFilters(props.filters)
const { t } = useI18n()

const onSort = ({ key, dir }) => {
    form.value.sort = key;
    form.value.direction = dir;
}

const isDeleteDialogOpen = ref(false)
const productToDelete = ref(null)

const confirmDelete = (product) => {
    productToDelete.value = product
    isDeleteDialogOpen.value = true
}

const deleteProduct = () => {
    if (productToDelete.value) {
        router.delete(route('products.destroy', productToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => isDeleteDialogOpen.value = false
        })
    }
}

const columns = [
  {
    id: 'product',
    accessorKey: 'name',
    header: t('products.name'),
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-3' }, [
      row.original.image_path 
        ? h('img', { src: `/storage/${row.original.image_path}`, class: 'w-10 h-10 rounded-md object-cover border dark:border-slate-700' })
        : h('div', { class: 'w-10 h-10 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400' }, [h(BoxIcon, { class: 'w-5 h-5' })]),
      h('span', { class: 'font-semibold' }, row.original.name)
    ])
  },
  {
    header: t('products.weight_col'),
    accessorKey: 'weight_grams'
  },
  {
    id: 'print_time',
    accessorKey: 'print_time_minutes',
    header: t('products.print_time_col'),
    cell: ({ row }) => {
        const { formattedPrintTime } = usePrintTime({ value: row.original.print_time_minutes })
        return h('span', { class: 'text-slate-500' }, formattedPrintTime.value)
    }
  },
  {
    id: 'price',
    accessorKey: 'price',
    header: t('products.price_col'),
    cell: ({ row }) => h('span', { class: 'font-mono font-bold text-orange-600' }, `${row.original.price} ${t('common.currency')}`)
  },
  {
    header: t('products.created_at'),
    accessorKey: 'created_at',
    cell: ({ row }) => new Date(row.original.created_at).toLocaleDateString()
  },
  {
    header: t('products.updated_at'),
    accessorKey: 'updated_at',
    cell: ({ row }) => new Date(row.original.updated_at).toLocaleDateString()
  },
  {
    id: 'actions',
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
        h(Link, { href: route('products.edit', row.original.id) }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(PencilIcon, { class: 'w-4 h-4 text-slate-500' }))),
        h(Button, { variant: 'ghost', size: 'icon', onClick: () => confirmDelete(row.original) }, () => h(TrashIcon, { class: 'w-4 h-4 text-red-500' }))
    ])
  }
]

const { columnVisibility, setColumnVisibility } = useColumnVisibility('products_table', columns)
</script>
