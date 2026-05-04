<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('printers.title') }}</span></template>
    
    <PageHeader :title="$t('printers.title')" description="">
      <template #actions>
        <Button class="bg-orange-500 hover:bg-orange-600 text-white" @click="openCreateDialog"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('printers.new') }}</Button>
      </template>
    </PageHeader>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="printer in printers" :key="printer.id" class="p-6 relative overflow-hidden">
        <div class="flex justify-between items-start mb-4">
          <div class="flex items-center">
            <PrinterIcon class="w-6 h-6 text-orange-500 mr-2" />
            <h3 class="text-lg font-bold">{{ printer.name }}</h3>
          </div>
          <div class="flex space-x-1">
            <Button variant="ghost" size="icon" @click="openEditDialog(printer)" class="h-8 w-8">
              <PencilIcon class="w-4 h-4 text-slate-500" />
            </Button>
            <Button variant="ghost" size="icon" @click="confirmDelete(printer)" class="h-8 w-8 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30">
              <TrashIcon class="w-4 h-4 text-slate-500" />
            </Button>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <div class="text-sm text-slate-500 mb-1">{{ $t('printers.total_hours') }}</div>
            <div class="text-2xl font-mono">{{ printer.total_working_hours }}h</div>
          </div>
          
          <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-100 dark:border-slate-800">
            <div class="flex justify-between items-center mb-2">
              <div class="text-sm font-medium">{{ $t('printers.nozzle_status') }}</div>
              <Badge variant="outline" :class="printer.current_nozzle_hours > 500 ? 'border-red-500 text-red-500' : 'border-green-500 text-green-500'">
                {{ printer.current_nozzle_hours }}h <span v-if="printer.current_nozzle_diameter" class="ml-1 text-xs opacity-75">({{ printer.current_nozzle_diameter }}mm)</span>
              </Badge>
            </div>
            <div class="text-xs text-slate-500 mb-3">
              {{ $t('printers.last_changed') }}: {{ printer.last_nozzle_change_date ? new Date(printer.last_nozzle_change_date).toLocaleDateString() : '-' }}
            </div>
            <Button variant="outline" size="sm" class="w-full text-xs h-8 dark:border-slate-700 dark:text-slate-200" @click="openNozzleDialog(printer)">
              <RotateCcwIcon class="w-3 h-3 mr-2" /> {{ $t('printers.change_nozzle') }}
            </Button>
          </div>
          
          <div class="flex gap-2">
            <Button 
              class="w-full text-xs" 
              :class="printer.needs_maintenance ? 'bg-red-500 hover:bg-red-600 text-white border-none' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'"
              @click="openMaintenanceDialog(printer)"
            >
              <div v-if="printer.needs_maintenance" class="w-2 h-2 rounded-full bg-white mr-2 animate-pulse"></div>
              <WrenchIcon v-else class="w-3 h-3 mr-2" /> 
              {{ $t('printers.maintenance_btn') }}
            </Button>
            
            <Link :href="route('printers.show', printer.id)" class="w-full">
              <Button variant="outline" class="w-full text-xs dark:border-slate-700 dark:text-slate-200">
                <HistoryIcon class="w-3 h-3 mr-2" /> {{ $t('printers.history_btn') }}
              </Button>
            </Link>
          </div>
        </div>
      </Card>
    </div>

    <!-- Empty State -->
    <div v-if="!printers.length" class="text-center py-12 bg-white dark:bg-slate-950 rounded-lg border border-slate-200 dark:border-slate-800 border-dashed mt-6">
      <PrinterIcon class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ $t('printers.empty_title') }}</h3>
      <p class="text-slate-500 mt-1 mb-4">{{ $t('printers.empty_desc') }}</p>
      <Button variant="outline" @click="openCreateDialog">{{ $t('printers.new') }}</Button>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="isDialogOpen">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle class="dark:text-white">{{ editingPrinter ? $t('printers.edit') : $t('printers.new') }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitForm">
          <div class="grid gap-4 py-4">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-slate-200">{{ $t('printers.name') }}</label>
              <Input v-model="form.name" required class="dark:bg-slate-900 dark:text-white dark:border-slate-700" />
              <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-slate-200">{{ $t('printers.total_hours') }}</label>
              <Input type="number" v-model="form.total_working_hours" required min="0" class="dark:bg-slate-900 dark:text-white dark:border-slate-700" />
              <div v-if="form.errors.total_working_hours" class="text-sm text-red-500">{{ form.errors.total_working_hours }}</div>
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="isDialogOpen = false" class="dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ $t('common.cancel') }}</Button>
            <Button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white border-none" :disabled="form.processing">{{ $t('common.save') }}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Change Nozzle Dialog -->
    <Dialog v-model:open="isNozzleDialogOpen">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle class="dark:text-white">{{ $t('printers.change_nozzle') }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitNozzleForm">
          <div class="grid gap-4 py-4">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-slate-200">{{ $t('printers.working_hours_at_change') }}</label>
              <Input type="number" v-model="nozzleForm.working_hours_at_change" required min="0" class="dark:bg-slate-900 dark:text-white dark:border-slate-700" />
              <div v-if="nozzleForm.errors.working_hours_at_change" class="text-sm text-red-500">{{ nozzleForm.errors.working_hours_at_change }}</div>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-slate-200">{{ $t('printers.nozzle_diameter') }}</label>
              <select v-model="nozzleForm.nozzle_diameter" required class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500">
                <option value="0.2">0.2 mm</option>
                <option value="0.4">0.4 mm</option>
                <option value="0.6">0.6 mm</option>
                <option value="0.8">0.8 mm</option>
              </select>
              <div v-if="nozzleForm.errors.nozzle_diameter" class="text-sm text-red-500">{{ nozzleForm.errors.nozzle_diameter }}</div>
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="isNozzleDialogOpen = false" class="dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ $t('common.cancel') }}</Button>
            <Button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white border-none" :disabled="nozzleForm.processing">{{ $t('printers.yes_change') }}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Maintenance Dialog -->
    <Dialog v-model:open="isMaintenanceDialogOpen">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle class="dark:text-white">{{ $t('printers.maintenance_btn') }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitMaintenanceForm">
          <div class="grid gap-4 py-4">
            <div class="space-y-2">
              <label class="text-sm font-medium dark:text-slate-200">{{ $t('printers.working_hours_at_change') }}</label>
              <Input type="number" v-model="maintenanceForm.working_hours_at_maintenance" required :min="maintenancePrinter?.total_working_hours" class="dark:bg-slate-900 dark:text-white dark:border-slate-700" />
              <div v-if="maintenanceForm.errors.working_hours_at_maintenance" class="text-sm text-red-500">{{ maintenanceForm.errors.working_hours_at_maintenance }}</div>
            </div>
            <div class="flex items-center space-x-2 mt-2">
              <input type="checkbox" id="lubricated" v-model="maintenanceForm.lubricated" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
              <label for="lubricated" class="text-sm font-medium dark:text-slate-200">{{ $t('printers.lubricated') }}</label>
            </div>
            <div v-if="maintenanceForm.errors.lubricated" class="text-sm text-red-500">{{ maintenanceForm.errors.lubricated }}</div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="isMaintenanceDialogOpen = false" class="dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ $t('common.cancel') }}</Button>
            <Button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white border-none" :disabled="maintenanceForm.processing">{{ $t('printers.record_maintenance') }}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirm -->
    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('printers.delete_confirm')"
        @confirm="deletePrinter"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog'
import { PrinterIcon, PlusIcon, PencilIcon, TrashIcon, RotateCcwIcon, WrenchIcon, HistoryIcon } from 'lucide-vue-next'

const props = defineProps({ printers: Array })

const isDialogOpen = ref(false)
const editingPrinter = ref(null)

const form = useForm({
    name: '',
    total_working_hours: 0,
})

const openCreateDialog = () => {
    editingPrinter.value = null
    form.reset()
    form.clearErrors()
    isDialogOpen.value = true
}

const openEditDialog = (printer) => {
    editingPrinter.value = printer
    form.name = printer.name
    form.total_working_hours = printer.total_working_hours
    form.clearErrors()
    isDialogOpen.value = true
}

const submitForm = () => {
    if (editingPrinter.value) {
        form.put(route('printers.update', editingPrinter.value.id), {
            onSuccess: () => {
              isDialogOpen.value = false
              form.reset()
            }
        })
    } else {
        form.post(route('printers.store'), {
            onSuccess: () => {
              isDialogOpen.value = false
              form.reset()
            }
        })
    }
}

const isDeleteDialogOpen = ref(false)
const printerToDelete = ref(null)

const confirmDelete = (printer) => {
    printerToDelete.value = printer
    isDeleteDialogOpen.value = true
}

const deletePrinter = () => {
    if (printerToDelete.value) {
        router.delete(route('printers.destroy', printerToDelete.value.id), {
            onSuccess: () => isDeleteDialogOpen.value = false
        })
    }
}

const isNozzleDialogOpen = ref(false)
const nozzlePrinter = ref(null)
const nozzleForm = useForm({
    working_hours_at_change: 0,
    nozzle_diameter: '0.4',
})

const openNozzleDialog = (printer) => {
    nozzlePrinter.value = printer
    nozzleForm.working_hours_at_change = printer.total_working_hours
    nozzleForm.nozzle_diameter = printer.current_nozzle_diameter ? printer.current_nozzle_diameter.toString() : '0.4'
    nozzleForm.clearErrors()
    isNozzleDialogOpen.value = true
}

const submitNozzleForm = () => {
    if (nozzlePrinter.value) {
        nozzleForm.post(route('printers.reset-nozzle', nozzlePrinter.value.id), {
            onSuccess: () => isNozzleDialogOpen.value = false
        })
    }
}

const isMaintenanceDialogOpen = ref(false)
const maintenancePrinter = ref(null)
const maintenanceForm = useForm({
    working_hours_at_maintenance: 0,
    lubricated: true,
})

const openMaintenanceDialog = (printer) => {
    maintenancePrinter.value = printer
    maintenanceForm.working_hours_at_maintenance = printer.total_working_hours
    maintenanceForm.lubricated = true
    maintenanceForm.clearErrors()
    isMaintenanceDialogOpen.value = true
}

const submitMaintenanceForm = () => {
    if (maintenancePrinter.value) {
        maintenanceForm.post(route('printers.maintenance', maintenancePrinter.value.id), {
            onSuccess: () => isMaintenanceDialogOpen.value = false
        })
    }
}
</script>
