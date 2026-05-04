<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('filaments.title') }}</span></template>
    
    <PageHeader :title="$t('filaments.title')" description="">
      <template #actions>
        <Link :href="route('filaments.create')">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('filaments.new') }}</Button>
        </Link>
      </template>
    </PageHeader>
    
    <DataTable :data="filaments.data" :columns="columns" :page-size="15" searchable :search-placeholder="$t('filaments.search_placeholder')" v-model="form.search" :pagination="filaments">
        <template #filters>
            <select v-model="form.material" class="h-9 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                <option value="">{{ $t('filaments.all_materials') }}</option>
                <option value="pla">PLA</option>
                <option value="petg">PETG</option>
            </select>
        </template>
    </DataTable>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('filaments.delete_confirm')"
        @confirm="deleteFilament"
    />
  </AppLayout>
</template>

<script setup>
import { ref, h } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import MaterialBadge from '@/Components/MaterialBadge.vue'
import ColorSwatch from '@/Components/ColorSwatch.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { PlusIcon, PencilIcon, TrashIcon } from 'lucide-vue-next'
import { useFilters } from '@/composables/useFilters'
import { toast } from 'vue-sonner'
import { useI18n } from 'vue-i18n'

const props = defineProps({ filaments: Object, filters: Object })
const { form } = useFilters(props.filters)
const page = usePage()
const { t } = useI18n()

const isDeleteDialogOpen = ref(false)
const targetItem = ref(null)

const confirmDelete = (item) => {
    targetItem.value = item
    isDeleteDialogOpen.value = true
}

const deleteFilament = () => {
    if (targetItem.value) {
        router.delete(route('filaments.destroy', targetItem.value.id), {
            preserveScroll: true,
            onError: () => {
                isDeleteDialogOpen.value = false
                toast.error(t('common.error_generic'))
            },
            onSuccess: () => {
                isDeleteDialogOpen.value = false
                const flashError = page.props.flash?.error
                const flashSuccess = page.props.flash?.success
                if (flashError) {
                    toast.error(flashError)
                } else if (flashSuccess) {
                    toast.success(flashSuccess)
                } else {
                    toast.success(t('filaments.deleted'))
                }
            }
        })
    }
}

const columns = [
  { header: t('filaments.brand'), accessorKey: 'brand' },
  { header: t('filaments.name'), cell: ({ row }) => h('span', { class: 'font-semibold' }, row.original.name) },
  {
    header: t('filaments.material'),
    cell: ({ row }) => h(MaterialBadge, { material: row.original.material })
  },
  {
    header: t('filaments.color_name'),
    cell: ({ row }) => h(ColorSwatch, { colorHex: row.original.color_hex || '#ccc', colorName: row.original.color_name || 'N/A' })
  },
  {
    header: t('filaments.price_per_kg'),
    cell: ({ row }) => h('span', { class: 'font-mono text-orange-600' }, `${row.original.price_per_kg}`)
  },
  {
    id: 'actions',
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
        h(Link, { href: route('filaments.edit', row.original.id) }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(PencilIcon, { class: 'w-4 h-4 text-slate-500' }))),
        h(Button, { variant: 'ghost', size: 'icon', onClick: () => confirmDelete(row.original) }, () => h(TrashIcon, { class: 'w-4 h-4 text-red-500' }))
    ])
  }
]
</script>
