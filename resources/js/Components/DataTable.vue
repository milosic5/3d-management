<template>
  <div class="space-y-4">
    <!-- Filters Row -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
        <Input 
            v-if="searchable"
            :placeholder="searchPlaceholder || $t('common.search')" 
            class="w-full sm:w-72"
            :model-value="modelValue" 
            @update:model-value="$emit('update:modelValue', $event)" 
        />
        <slot name="filters" />
      </div>
      <div class="w-full sm:w-auto flex justify-end">
        <slot name="actions" />
      </div>
    </div>

    <!-- Table content -->
    <div class="rounded-md border bg-white dark:bg-slate-950 dark:border-slate-800 overflow-hidden shadow-sm w-full">
      <div class="overflow-x-auto">
      <Table>
        <TableHeader class="bg-slate-50 dark:bg-slate-900/50">
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead 
              v-for="header in headerGroup.headers" 
              :key="header.id" 
              class="whitespace-nowrap font-semibold"
              :class="(header.column.getCanSort() || (pagination && header.column.columnDef.accessorKey)) ? 'cursor-pointer select-none hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' : ''"
              @click="onHeaderClick(header)"
            >
              <div class="flex items-center gap-1">
                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                <span v-if="header.column.getCanSort() || (pagination && header.column.columnDef.accessorKey)" class="text-slate-400">
                  <template v-if="pagination">
                    <span v-if="serverSortKey === header.column.columnDef.accessorKey && serverSortDir === 'asc'">↑</span>
                    <span v-else-if="serverSortKey === header.column.columnDef.accessorKey && serverSortDir === 'desc'">↓</span>
                    <span v-else class="opacity-40">↕</span>
                  </template>
                  <template v-else>
                    <span v-if="header.column.getIsSorted() === 'asc'">↑</span>
                    <span v-else-if="header.column.getIsSorted() === 'desc'">↓</span>
                    <span v-else class="opacity-40">↕</span>
                  </template>
                </span>
              </div>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow 
            v-for="row in table.getRowModel().rows" 
            :key="row.id" 
            :class="[
              'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors',
              rowClickable ? 'cursor-pointer select-none' : ''
            ]"
            @click="rowClickable && $emit('row-click', row.original)"
          >
            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="align-middle">
              <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
            </TableCell>
          </TableRow>
          <TableRow v-if="!table.getRowModel().rows.length">
            <TableCell :colspan="columns.length" class="h-32 text-center text-slate-500">
                <slot name="empty">
                    <div class="flex flex-col items-center justify-center py-6">
                        <InboxIcon class="w-10 h-10 mb-3 opacity-20" />
                        <p class="text-sm">{{ $t('common.no_results') }}</p>
                    </div>
                </slot>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
      </div>
    </div>

    <!-- Server-side pagination (Laravel paginator) -->
    <div v-if="pagination && pagination.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between py-4 gap-4">
      <div class="text-xs text-slate-500 ml-1">
        {{ $t('common.page') }} {{ pagination.current_page }} {{ $t('common.of') }} {{ pagination.last_page }}
        <span class="ml-2 text-slate-400">({{ pagination.total }} {{ $t('common.total', 'total') }})</span>
      </div>
      <div class="flex items-center space-x-2">
        <Link :href="pagination.prev_page_url ?? '#'">
          <Button variant="outline" size="sm" :disabled="!pagination.prev_page_url">{{ $t('common.previous') }}</Button>
        </Link>
        <Link :href="pagination.next_page_url ?? '#'">
          <Button variant="outline" size="sm" :disabled="!pagination.next_page_url">{{ $t('common.next') }}</Button>
        </Link>
      </div>
    </div>

    <!-- Client-side pagination (when no server paginator provided) -->
    <div v-else-if="!pagination && table.getPageCount() > 1" class="flex flex-col sm:flex-row items-center justify-between py-4 gap-4">
      <div class="text-xs text-slate-500 ml-1">
        {{ $t('common.page') }} {{ table.getState().pagination.pageIndex + 1 }} {{ $t('common.of') }} {{ table.getPageCount() }}
      </div>
      <div class="flex items-center space-x-2">
        <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">{{ $t('common.previous') }}</Button>
        <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">{{ $t('common.next') }}</Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  FlexRender,
  getCoreRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { InboxIcon } from 'lucide-vue-next'

const props = defineProps({
  data: { type: Array, required: true },
  columns: { type: Array, required: true },
  pageSize: { type: Number, default: 15 },
  searchable: { type: Boolean, default: false },
  searchPlaceholder: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  columnVisibility: { type: Object, default: () => ({}) },
  pagination: { type: Object, default: null },
  serverSort: { type: Object, default: null },
  rowClickable: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'update:columnVisibility', 'sort', 'row-click'])

const sorting = ref([])

// Server-sort state (visual indicators when using server-side pagination)
const serverSortKey = ref(props.serverSort?.key ?? '')
const serverSortDir = ref(props.serverSort?.dir ?? '')

function onHeaderClick(header) {
  const key = header.column.columnDef.accessorKey
  if (props.pagination && key) {
    // Server-side sort: cycle asc → desc → none
    if (serverSortKey.value === key) {
      if (serverSortDir.value === 'asc') {
        serverSortDir.value = 'desc'
      } else if (serverSortDir.value === 'desc') {
        serverSortKey.value = ''
        serverSortDir.value = ''
      } else {
        serverSortDir.value = 'asc'
      }
    } else {
      serverSortKey.value = key
      serverSortDir.value = 'asc'
    }
    emit('sort', { key: serverSortKey.value, dir: serverSortDir.value })
  } else if (header.column.getCanSort()) {
    header.column.toggleSorting()
  }
}

const table = useVueTable({
  get data() { return props.data },
  get columns() { return props.columns },
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  onColumnVisibilityChange: (updater) => emit('update:columnVisibility', updater),
  onSortingChange: (updater) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater
  },
  state: {
    get columnVisibility() { return props.columnVisibility },
    get sorting() { return sorting.value },
  },
  initialState: {
    pagination: {
      pageSize: props.pageSize,
    },
  },
})
</script>
