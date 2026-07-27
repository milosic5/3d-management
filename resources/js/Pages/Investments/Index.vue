<template>
  <AppLayout>
    <template #breadcrumb>
        <span class="text-slate-500 font-medium">{{ $t('investments.title') }}</span>
    </template>
    
    <PageHeader :title="$t('investments.title')" :description="$t('investments.manage_expenses_desc', 'Track hardware investments, packaging, and business expenses.')">
      <template #actions>
        <a :href="route('export.investments.excel')" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors mr-2">
            <DownloadIcon class="w-4 h-4 mr-1.5 text-green-600" /> Excel
        </a>
        <Link :href="route('investments.categories.index')">
            <Button variant="outline" class="mr-2"><SettingsIcon class="w-4 h-4 mr-2" /> {{ $t('investments.categories') }}</Button>
        </Link>
        <Link :href="route('investments.create')">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('investments.new') }}</Button>
        </Link>
      </template>
    </PageHeader>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card class="p-4 bg-slate-50 dark:bg-slate-900 border-none">
            <p class="text-sm font-medium text-slate-500">{{ $t('investments.total_invested') }}</p>
            <p class="text-2xl font-mono font-bold text-slate-800 dark:text-slate-100">{{ parseFloat(summary.totalInvested).toFixed(2) }} {{ $t('common.currency') }}</p>
        </Card>
    </div>

    <DataTable 
      :data="investments.data" 
      :columns="columns" 
      :page-size="15" 
      searchable 
      :search-placeholder="$t('common.search')" 
      v-model="form.search" 
      :pagination="investments"
      :server-sort="{ key: filters.sort ?? '', dir: filters.dir ?? '' }"
      @sort="onSort"
    >
        <template #filters>
            <select v-model="form.category" class="h-9 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                <option value="">{{ $t('investments.filter_category') }}</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </template>
    </DataTable>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('investments.delete_confirm')"
        @confirm="deleteInvestment"
    />
  </AppLayout>
</template>

<script setup>
import { ref, h } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import { Card } from '@/Components/ui/card'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { PlusIcon, SettingsIcon, TrashIcon, PencilIcon, DownloadIcon } from 'lucide-vue-next'
import { useFilters } from '@/composables/useFilters'

import { useI18n } from 'vue-i18n'

const props = defineProps({ investments: Object, categories: Array, filters: Object, summary: Object })
const { form } = useFilters(props.filters)
const { t, locale } = useI18n()

const isDeleteDialogOpen = ref(false)
const targetItem = ref(null)

const confirmDelete = (item) => {
    targetItem.value = item
    isDeleteDialogOpen.value = true
}

const deleteInvestment = () => {
    if (targetItem.value) {
        router.delete(route('investments.destroy', targetItem.value.id), {
            preserveScroll: true,
            onSuccess: () => isDeleteDialogOpen.value = false
        })
    }
}

const onSort = ({ key, dir }) => {
    router.get(window.location.pathname, {
        ...form.value,
        sort: key || undefined,
        dir: dir || undefined,
    }, { preserveState: true, replace: true })
}

const columns = [
  { 
    accessorKey: 'invested_at',
    header: t('investments.invested_at'), 
    cell: ({ row }) => new Date(row.original.invested_at).toLocaleDateString() 
  },
  { 
    accessorKey: 'name',
    header: t('investments.name'), 
    cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.name) 
  },
  { 
    accessorFn: (row) => row.category?.name ?? '',
    id: 'category',
    header: t('investments.category'), 
    cell: ({ row }) => h('div', { class: 'inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-1 text-xs font-medium' }, row.original.category.name) 
  },
  { 
    accessorKey: 'quantity',
    header: t('investments.quantity'), 
    cell: ({ row }) => {
      const unit = locale.value === 'sr' ? 'kom' : 'pcs'
      return `${parseInt(row.original.quantity)} ${unit}`
    }
  },
  { 
    accessorKey: 'amount',
    header: t('investments.total_invested'), 
    cell: ({ row }) => h('span', { class: 'font-mono text-orange-600 font-bold' }, `${parseFloat(row.original.amount).toFixed(2)} ${t('common.currency')}`) 
  },
  {
    id: 'actions',
    enableSorting: false,
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
        h(Link, { href: route('investments.edit', row.original.id) }, () => 
            h(Button, { variant: 'ghost', size: 'icon' }, () => h(PencilIcon, { class: 'w-4 h-4 text-slate-500' }))
        ),
        h(Button, { variant: 'ghost', size: 'icon', onClick: () => confirmDelete(row.original) }, () => h(TrashIcon, { class: 'w-4 h-4 text-red-500' }))
    ])
  }
]
</script>
