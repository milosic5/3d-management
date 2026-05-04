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
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { WrenchIcon, RotateCcwIcon } from 'lucide-vue-next'

const props = defineProps({
    printer: {
        type: Object,
        required: true
    }
})
</script>
