<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('calibrations.title') }}</span></template>
    
    <PageHeader :title="$t('calibrations.title')" description="">
      <template #actions>
        <Link :href="route('calibrations.create')">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('calibrations.new') }}</Button>
        </Link>
      </template>
    </PageHeader>

    <!-- Brand Filter Toggles -->
    <div class="mb-6 flex flex-wrap gap-2 items-center bg-white dark:bg-slate-950 p-4 rounded-md border border-slate-200 dark:border-slate-800 shadow-sm">
        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mr-2 flex items-center">
            <FilterIcon class="w-4 h-4 mr-1"/> Filter:
        </span>
        <button 
            v-for="brand in topBrands" 
            :key="brand"
            @click="toggleBrand(brand)"
            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors border"
            :class="selectedBrands.includes(brand) 
                ? 'bg-orange-500 text-white border-orange-500' 
                : 'bg-transparent text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
            {{ brand }}
        </button>
        <button 
            @click="toggleBrand('others')"
            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors border ml-1"
            :class="selectedBrands.includes('others') 
                ? 'bg-orange-500 text-white border-orange-500' 
                : 'bg-transparent text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
            {{ $t('calibrations.brand_filter_others') }}
        </button>
    </div>
    
    <div class="rounded-md border bg-white dark:bg-slate-950 dark:border-slate-800 overflow-hidden shadow-sm">
      <Table>
        <TableHeader class="bg-slate-50 dark:bg-slate-900/50">
          <TableRow>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('color_name')">
                <div class="flex items-center">Boja <SortIndicator column="color_name" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('brand')">
                <div class="flex items-center">Brend <SortIndicator column="brand" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('filament_name')">
                <div class="flex items-center">Naziv <SortIndicator column="filament_name" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('temperature')">
                <div class="flex items-center">Temp <SortIndicator column="temperature" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('flow_ratio')">
                <div class="flex items-center">Flow Ratio <SortIndicator column="flow_ratio" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('pressure_advance')">
                <div class="flex items-center">Pressure Advance <SortIndicator column="pressure_advance" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="whitespace-nowrap font-semibold cursor-pointer select-none" @click="sortBy('max_volumetric_speed')">
                <div class="flex items-center">Max Vol. Speed <SortIndicator column="max_volumetric_speed" :currentSort="currentSort" :currentDirection="currentDirection" /></div>
            </TableHead>
            <TableHead class="w-[100px]"></TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in calibrations.data" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
            <TableCell>
                <ColorSwatch :colorHex="item.filament_color_hex || '#ccc'" :colorName="item.filament_color_name || 'N/A'" />
            </TableCell>
            <TableCell class="font-medium">{{ item.filament_brand }}</TableCell>
            <TableCell class="font-semibold">{{ item.filament_name }}</TableCell>
            <TableCell>
                <span v-if="item.temperature">{{ item.temperature }} °C</span>
                <span v-else class="text-slate-400">-</span>
            </TableCell>
            <TableCell class="font-mono text-orange-600 border-orange-200">
                <span v-if="item.flow_ratio">{{ Number(item.flow_ratio).toFixed(4) }}</span>
                <span v-else class="text-slate-400">-</span>
            </TableCell>
            <TableCell class="font-mono text-blue-600">
                <span v-if="item.pressure_advance">{{ Number(item.pressure_advance).toFixed(4) }}</span>
                <span v-else class="text-slate-400">-</span>
            </TableCell>
            <TableCell class="font-mono">
                <span v-if="item.max_volumetric_speed">{{ Number(item.max_volumetric_speed).toFixed(2) }} mm³/s</span>
                <span v-else class="text-slate-400">-</span>
            </TableCell>
            <TableCell>
                <div class="flex items-center justify-end space-x-2">
                    <Link :href="route('calibrations.edit', item.id)">
                        <Button variant="ghost" size="icon"><PencilIcon class="w-4 h-4 text-slate-500" /></Button>
                    </Link>
                    <Button variant="ghost" size="icon" @click="confirmDelete(item)">
                        <TrashIcon class="w-4 h-4 text-red-500" />
                    </Button>
                </div>
            </TableCell>
          </TableRow>
          <TableRow v-if="!calibrations.data.length">
            <TableCell colspan="8" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center py-6">
                    <InboxIcon class="w-10 h-10 mb-3 opacity-20" />
                    <p class="text-sm">{{ $t('common.no_results') }}</p>
                </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
      
      <!-- Pagination Controls for tailwind -->
      <div v-if="calibrations.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
        <div class="text-sm text-slate-500">
          Showing {{ calibrations.from }} to {{ calibrations.to }} of {{ calibrations.total }} results
        </div>
        <div class="flex items-center space-x-2">
            <template v-for="(link, i) in calibrations.links" :key="i">
                <Link v-if="link.url" :href="link.url">
                    <Button variant="outline" size="sm" :class="{ 'bg-orange-50 text-orange-600 border-orange-200': link.active }" v-html="link.label"></Button>
                </Link>
                <Button v-else variant="outline" size="sm" disabled v-html="link.label"></Button>
            </template>
        </div>
      </div>
    </div>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('calibrations.delete_confirm')"
        @confirm="deleteCalibration"
    />
  </AppLayout>
</template>

<script setup>
import { ref, watch, h } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ColorSwatch from '@/Components/ColorSwatch.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { PlusIcon, PencilIcon, TrashIcon, InboxIcon, FilterIcon, ArrowUpIcon, ArrowDownIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useI18n } from 'vue-i18n'
import debounce from 'lodash/debounce'

const props = defineProps({ calibrations: Object, topBrands: Array, filters: Object })
const page = usePage()
const { t } = useI18n()

// Filters state
const selectedBrands = ref(props.filters.brands || [])
const currentSort = ref(props.filters.sort || 'created_at')
const currentDirection = ref(props.filters.direction || 'desc')

const SortIndicator = (props) => {
    if (props.column !== props.currentSort) {
        return h('span', { class: 'opacity-0 group-hover:opacity-50 ml-1 inline-block' }, [h(ArrowUpIcon, { class: 'w-3 h-3' })])
    }
    return h('span', { class: 'ml-1 inline-block text-orange-500' }, [
        props.currentDirection === 'asc' ? h(ArrowUpIcon, { class: 'w-3 h-3' }) : h(ArrowDownIcon, { class: 'w-3 h-3' })
    ])
}

const toggleBrand = (brand) => {
    if (selectedBrands.value.includes(brand)) {
        selectedBrands.value = selectedBrands.value.filter(b => b !== brand)
    } else {
        selectedBrands.value.push(brand)
    }
    applyFilters()
}

const sortBy = (column) => {
    if (currentSort.value === column) {
        currentDirection.value = currentDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        currentSort.value = column
        currentDirection.value = 'asc'
    }
    applyFilters()
}

const applyFilters = () => {
    router.get(route('calibrations.index'), {
        brands: selectedBrands.value,
        sort: currentSort.value,
        direction: currentDirection.value
    }, { preserveState: true, preserveScroll: true })
}

const isDeleteDialogOpen = ref(false)
const targetItem = ref(null)

const confirmDelete = (item) => {
    targetItem.value = item
    isDeleteDialogOpen.value = true
}

const deleteCalibration = () => {
    if (targetItem.value) {
        router.delete(route('calibrations.destroy', targetItem.value.id), {
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
                    toast.success('Calibration deleted successfully.')
                }
            }
        })
    }
}
</script>
