<template>
  <AppLayout>
    <template #breadcrumb>
      <div class="flex items-center space-x-2">
        <Link :href="route('printers.index')" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium transition-colors">{{ $t('printers.title') }}</Link>
        <span class="text-slate-400">/</span>
        <span class="text-slate-900 dark:text-slate-100 font-medium">{{ printer.name }}</span>
      </div>
    </template>
    
    <PageHeader :title="printer.name" :description="`${$t('printers.total_hours')}: ${printer.total_working_hours}h`">
      <template #actions>
        <Link :href="route('printers.index')">
          <Button variant="outline" class="dark:border-slate-700 dark:text-slate-200">
            {{ $t('common.cancel') }}
          </Button>
        </Link>
      </template>
    </PageHeader>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
      <!-- Maintenance History -->
      <Card class="p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
          <h3 class="text-lg font-semibold flex items-center">
            <WrenchIcon class="w-5 h-5 mr-2 text-orange-500" />
            {{ $t('printers.maintenance_btn') }}
          </h3>
        </div>
        <div class="p-0">
          <div v-if="!printer.maintenances || !printer.maintenances.length" class="p-8 text-center text-slate-500">
            Nema istorije održavanja.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                <tr>
                  <th class="px-6 py-3">Mesec</th>
                  <th class="px-6 py-3">Upisani sati</th>
                  <th class="px-6 py-3">Sati u mesecu</th>
                  <th class="px-6 py-3">Podmazan</th>
                  <th class="px-6 py-3 text-right">Akcije</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in printer.maintenances" :key="m.id" class="border-b border-slate-50 dark:border-slate-800/50 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                  <td class="px-6 py-4 font-medium">{{ new Date(m.maintenance_month).toLocaleDateString(undefined, { month: 'long', year: 'numeric' }) }}</td>
                  <td class="px-6 py-4">{{ m.working_hours_at_maintenance }}h</td>
                  <td class="px-6 py-4 text-orange-600 dark:text-orange-400 font-medium">+{{ m.hours_printed_this_month }}h</td>
                  <td class="px-6 py-4">
                    <Badge v-if="m.lubricated" class="bg-green-100 text-green-700 hover:bg-green-100 border-none">Da</Badge>
                    <Badge v-else class="bg-red-100 text-red-700 hover:bg-red-100 border-none">Ne</Badge>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button @click="openEditModal(m)" class="text-blue-500 hover:text-blue-700 mr-3" title="Izmeni">
                      <PencilIcon class="w-4 h-4 inline" />
                    </button>
                    <button @click="confirmDelete(m)" class="text-red-500 hover:text-red-700" title="Obriši">
                      <TrashIcon class="w-4 h-4 inline" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Card>

      <!-- Nozzle History -->
      <Card class="p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
          <h3 class="text-lg font-semibold flex items-center">
            <RotateCcwIcon class="w-5 h-5 mr-2 text-blue-500" />
            {{ $t('printers.change_nozzle') }}
          </h3>
        </div>
        <div class="p-0">
          <div v-if="!printer.nozzle_changes || !printer.nozzle_changes.length" class="p-8 text-center text-slate-500">
            Nema istorije menjanja dizni.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                <tr>
                  <th class="px-6 py-3">Datum</th>
                  <th class="px-6 py-3">Prečnik</th>
                  <th class="px-6 py-3">Radni sati</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="n in printer.nozzle_changes" :key="n.id" class="border-b border-slate-50 dark:border-slate-800/50 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                  <td class="px-6 py-4 font-medium">{{ new Date(n.created_at).toLocaleDateString() }}</td>
                  <td class="px-6 py-4"><Badge variant="outline">{{ n.nozzle_diameter }}mm</Badge></td>
                  <td class="px-6 py-4 text-blue-600 dark:text-blue-400">{{ n.working_hours_at_change }}h</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Card>
    </div>
    
    <Modal :show="isEditModalOpen" @close="closeEditModal" maxWidth="md">
      <div class="p-6">
        <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-4 flex items-center">
          <PencilIcon class="w-5 h-5 mr-2 text-blue-500" />
          Izmeni održavanje
        </h2>
        <form @submit.prevent="submitEdit">
          <div class="mb-4">
            <InputLabel for="maintenance_month" value="Mesec" />
            <TextInput id="maintenance_month" type="month" class="mt-1 block w-full" v-model="editForm.maintenance_month" required />
          </div>
          <div class="mb-4">
            <InputLabel for="working_hours" value="Upisani sati (ukupni)" />
            <TextInput id="working_hours" type="number" class="mt-1 block w-full" v-model="editForm.working_hours_at_maintenance" required />
          </div>
          <div class="mb-4">
            <InputLabel for="hours_printed" value="Sati u mesecu (razlika)" />
            <TextInput id="hours_printed" type="number" class="mt-1 block w-full" v-model="editForm.hours_printed_this_month" required />
          </div>
          <div class="mb-6 flex items-center mt-6">
            <Checkbox id="lubricated" v-model:checked="editForm.lubricated" />
            <InputLabel for="lubricated" value="Podmazan?" class="ml-2 mb-0 cursor-pointer" />
          </div>
          <div class="flex items-center justify-end mt-4">
            <SecondaryButton @click="closeEditModal" class="mr-2">Otkaži</SecondaryButton>
            <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">Sačuvaj</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <ConfirmDialog 
      v-model:isOpen="isDeleteDialogOpen"
      title="Brisanje održavanja"
      description="Da li ste sigurni da želite da obrišete ovaj zapis o održavanju? Ova akcija je nepovratna."
      :processing="isDeleting"
      @confirm="deleteMaintenance"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { WrenchIcon, RotateCcwIcon, PencilIcon, TrashIcon } from 'lucide-vue-next'
import Modal from '@/Components/Modal.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import Checkbox from '@/Components/Checkbox.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    printer: {
        type: Object,
        required: true
    }
})

const isEditModalOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const isDeleting = ref(false)
const maintenanceToEdit = ref(null)
const maintenanceToDelete = ref(null)

const editForm = useForm({
  maintenance_month: '',
  working_hours_at_maintenance: 0,
  hours_printed_this_month: 0,
  lubricated: false,
})

const openEditModal = (m) => {
  maintenanceToEdit.value = m
  const dateObj = new Date(m.maintenance_month)
  const monthStr = String(dateObj.getMonth() + 1).padStart(2, '0')
  editForm.maintenance_month = `${dateObj.getFullYear()}-${monthStr}`
  editForm.working_hours_at_maintenance = m.working_hours_at_maintenance
  editForm.hours_printed_this_month = m.hours_printed_this_month
  editForm.lubricated = m.lubricated ? true : false
  isEditModalOpen.value = true
}

const closeEditModal = () => {
  isEditModalOpen.value = false
  editForm.reset()
}

const submitEdit = () => {
  editForm.put(route('maintenances.update', maintenanceToEdit.value.id), {
    preserveScroll: true,
    onSuccess: () => closeEditModal(),
  })
}

const confirmDelete = (m) => {
  maintenanceToDelete.value = m
  isDeleteDialogOpen.value = true
}

const deleteMaintenance = () => {
  isDeleting.value = true
  router.delete(route('maintenances.destroy', maintenanceToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      isDeleteDialogOpen.value = false
      isDeleting.value = false
    },
    onError: () => {
      isDeleting.value = false
    }
  })
}
</script>
