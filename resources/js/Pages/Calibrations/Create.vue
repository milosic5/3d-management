<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('calibrations.index')" class="hover:underline text-slate-500">{{ $t('calibrations.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('calibrations.new') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('calibrations.new')" description="" />

    <form @submit.prevent="submit" class="max-w-2xl">
      <Card class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">{{ $t('calibrations.filament') }} *</label>
                <select v-model="form.filament_id" required class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="" disabled>{{ $t('calibrations.select_filament') }}</option>
                    <option v-for="filament in availableFilaments" :key="filament.id" :value="filament.id">
                        {{ filament.brand }} - {{ filament.name }} ({{ filament.color_name }})
                    </option>
                </select>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.filament_id">{{ form.errors.filament_id }}</div>
                <div v-if="!availableFilaments.length" class="text-amber-500 text-sm mt-1">
                    No available filaments found. All filaments either already have a calibration profile or none exist.
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('calibrations.temperature') }} (°C) *</label>
                <Input type="number" v-model="form.temperature" required min="0" max="500" class="w-full" placeholder="e.g. 210" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.temperature">{{ form.errors.temperature }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('calibrations.flow_ratio') }} *</label>
                <Input type="number" step="0.0001" v-model="form.flow_ratio" required min="0" class="w-full" placeholder="e.g. 0.9800" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.flow_ratio">{{ form.errors.flow_ratio }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('calibrations.pressure_advance') }} *</label>
                <Input type="number" step="0.0001" v-model="form.pressure_advance" required min="0" class="w-full" placeholder="e.g. 0.0450" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.pressure_advance">{{ form.errors.pressure_advance }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('calibrations.max_volumetric_speed') }} (mm³/s) *</label>
                <Input type="number" step="0.01" v-model="form.max_volumetric_speed" required min="0" class="w-full" placeholder="e.g. 15.00" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.max_volumetric_speed">{{ form.errors.max_volumetric_speed }}</div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <Link :href="route('calibrations.index')">
                <Button type="button" variant="outline">{{ $t('common.cancel') }}</Button>
            </Link>
            <Button class="bg-orange-500 hover:bg-orange-600 text-white" type="submit" :disabled="form.processing || !availableFilaments?.length">
                 {{ form.processing ? $t('common.loading') : $t('calibrations.new') }}
            </Button>
        </div>
      </Card>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    availableFilaments: {
        type: Array,
        default: () => []
    }
})

const { t } = useI18n()

const form = useForm({
    filament_id: '',
    temperature: 210,
    flow_ratio: 0.9800,
    pressure_advance: 0.0450,
    max_volumetric_speed: 15.00
})

const submit = () => {
    form.post(route('calibrations.store'))
}
</script>
