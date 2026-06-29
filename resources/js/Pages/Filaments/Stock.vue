<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('filaments.stock') || 'Filament Stock' }}</span></template>
    
    <PageHeader :title="$t('filaments.stock') || 'Filament Stock'" :description="$t('filaments.stock_description') || 'Manage your current filament stock levels.'">
    </PageHeader>
    
    <!-- Color Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <Card class="shadow-sm">
        <CardHeader class="pb-2 border-b border-slate-100 dark:border-slate-800">
          <CardTitle class="text-sm font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-300">
            PETG Zalihe (Presek)
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-4 grid grid-cols-5 gap-2 text-center">
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.petg.black }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-800 dark:bg-black border border-slate-300 dark:border-slate-600"></span> Crna</div>
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.petg.white }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-white border border-slate-300 dark:border-slate-600"></span> Bela</div>
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.petg.gray }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-400 border border-slate-300 dark:border-slate-600"></span> Siva</div>
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.petg.green }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 border border-slate-300 dark:border-slate-600"></span> Zelena</div>
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.petg.beige }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-100 border border-slate-300 dark:border-slate-600"></span> Bež</div>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow-sm">
        <CardHeader class="pb-2 border-b border-slate-100 dark:border-slate-800">
          <CardTitle class="text-sm font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-300">
            PLA Zalihe (Presek)
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-4 grid grid-cols-3 gap-2 text-center">
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.pla.black }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-800 dark:bg-black border border-slate-300 dark:border-slate-600"></span> Crna</div>
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.pla.white }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-white border border-slate-300 dark:border-slate-600"></span> Bela</div>
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ summary.pla.gray }}</div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-400 border border-slate-300 dark:border-slate-600"></span> Siva</div>
          </div>
        </CardContent>
      </Card>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <Card id="stock-form-card" class="md:col-span-1 shadow-sm h-fit">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
          <CardTitle class="text-lg font-semibold flex items-center gap-2">
            <PackagePlusIcon class="w-5 h-5 text-indigo-500" />
            {{ $t('filaments.update_stock') || 'Update Stock' }}
          </CardTitle>
          <CardDescription class="text-xs">
            {{ $t('filaments.update_stock_desc') || 'Select a filament and set the current number of rolls in stock.' }}
          </CardDescription>
        </CardHeader>
        <CardContent class="pt-5">
          <form @submit.prevent="submitStock" class="space-y-4">
            <div class="space-y-2">
              <label for="filament" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ $t('filaments.select_filament') || 'Select Filament' }}</label>
              <select 
                id="filament" 
                v-model="form.filament_id"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:bg-slate-950 dark:ring-offset-slate-950 dark:placeholder:text-slate-400 dark:focus-visible:ring-slate-300"
                @change="onFilamentChange"
                required
              >
                <option value="" disabled>{{ $t('common.select') || 'Select...' }}</option>
                <option v-for="f in filaments" :key="f.id" :value="f.id">
                  {{ f.brand }} - {{ f.name }} ({{ f.material }})
                </option>
              </select>
            </div>
            
            <div class="space-y-2">
              <label for="stock_rolls" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ $t('filaments.rolls_in_stock') || 'Rolls in Stock' }}</label>
              <Input 
                id="stock_rolls" 
                v-model.number="form.stock_rolls" 
                type="number" 
                min="0" 
                required 
              />
            </div>
            
            <Button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white" :disabled="form.processing">
              {{ $t('common.save') || 'Save' }}
            </Button>
          </form>
        </CardContent>
      </Card>

      <div id="stock-table-container" class="md:col-span-2">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200">
            {{ $t('filaments.stock_list', 'Lista zaliha') }}
          </h3>
          <div class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 px-3 py-1.5 rounded-md font-semibold flex items-center gap-2 shadow-sm border border-indigo-100 dark:border-indigo-800">
            <PackagePlusIcon class="w-4 h-4" />
            <span>{{ totalStockKg }} kg {{ $t('common.total', 'ukupno') }}</span>
          </div>
        </div>
        <DataTable :data="filteredFilaments" :columns="columns" :page-size="15" searchable :search-placeholder="$t('filaments.search_placeholder') || 'Search filaments...'" v-model="searchQuery" row-clickable @row-click="handleRowClick">
          <template #filters>
            <select v-model="materialFilter" class="h-9 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                <option value="">{{ $t('filaments.all_materials') || 'All Materials' }}</option>
                <option value="pla">PLA</option>
                <option value="petg">PETG</option>
            </select>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, h, computed, onMounted, onUnmounted } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import MaterialBadge from '@/Components/MaterialBadge.vue'
import ColorSwatch from '@/Components/ColorSwatch.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { PackagePlusIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useI18n } from 'vue-i18n'

const props = defineProps({ filaments: Array })
const { t } = useI18n()

const searchQuery = ref('')
const materialFilter = ref('')

// Compute filtered filaments for the datatable if we want client-side search since we disabled inertia pagination
const filteredFilaments = computed(() => {
  return props.filaments
    .filter(f => {
      const matchesSearch = (f.name.toLowerCase() + f.brand.toLowerCase()).includes(searchQuery.value.toLowerCase())
      const matchesMaterial = materialFilter.value ? f.material === materialFilter.value : true
      return matchesSearch && matchesMaterial
    })
    .sort((a, b) => (b.stock_rolls || 0) - (a.stock_rolls || 0))
})

const totalStockKg = computed(() => {
  return filteredFilaments.value.reduce((total, f) => total + (f.stock_rolls || 0), 0)
})

const getSummary = (material, colorKeywords) => {
  return props.filaments.reduce((total, f) => {
    if (f.material?.toLowerCase() === material.toLowerCase()) {
      const colorName = (f.color_name || '').toLowerCase()
      if (colorKeywords.some(keyword => colorName.includes(keyword))) {
        return total + (f.stock_rolls || 0)
      }
    }
    return total
  }, 0)
}

const summary = computed(() => {
  return {
    petg: {
      black: getSummary('petg', ['crn', 'black', 'blk']),
      white: getSummary('petg', ['bel', 'white', 'wht']),
      gray: getSummary('petg', ['siv', 'gray', 'grey']),
      green: getSummary('petg', ['zelen', 'green']),
      beige: getSummary('petg', ['bez', 'bež', 'beige']),
    },
    pla: {
      black: getSummary('pla', ['crn', 'black', 'blk']),
      white: getSummary('pla', ['bel', 'white', 'wht']),
      gray: getSummary('pla', ['siv', 'gray', 'grey']),
    }
  }
})

const form = useForm({
  filament_id: '',
  stock_rolls: 0
})

const onFilamentChange = () => {
  const selected = props.filaments.find(f => f.id === form.filament_id)
  if (selected) {
    form.stock_rolls = selected.stock_rolls || 0
  }
}

const handleRowClick = (filament) => {
  form.filament_id = filament.id
  form.stock_rolls = filament.stock_rolls || 0
}

const handleOutsideClick = (event) => {
  if (!form.filament_id) return
  
  const clickedFormCard = event.target.closest('#stock-form-card')
  const clickedTable = event.target.closest('#stock-table-container')
  
  if (!clickedFormCard && !clickedTable) {
    form.reset()
  }
}

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
})

const submitStock = () => {
  if (!form.filament_id) return
  
  form.post(route('filaments.update-stock', form.filament_id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.success(t('filaments.stock_updated') || 'Stock updated successfully')
      form.reset()
    },
    onError: () => {
      toast.error(t('common.error_generic') || 'An error occurred')
    }
  })
}

const columns = [
  { header: t('filaments.brand') || 'Brand', accessorKey: 'brand' },
  { header: t('filaments.name') || 'Name', accessorKey: 'name', cell: ({ row }) => h('span', { class: 'font-semibold' }, row.original.name) },
  {
    header: t('filaments.material') || 'Material',
    accessorKey: 'material',
    cell: ({ row }) => h(MaterialBadge, { material: row.original.material })
  },
  {
    header: t('filaments.color') || 'Color',
    accessorKey: 'color_name',
    cell: ({ row }) => h(ColorSwatch, { colorHex: row.original.color_hex || '#ccc', colorName: row.original.color_name || 'N/A' })
  },
  {
    header: t('filaments.stock') || 'Stock',
    accessorKey: 'stock_rolls',
    cell: ({ row }) => {
      const stock = row.original.stock_rolls || 0
      return h('span', { 
        class: [
          'font-semibold px-2 py-1 rounded-md text-sm',
          stock > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
        ]
      }, `${stock} ${t('filaments.rolls') || 'rolls'}`)
    }
  }
]
</script>
