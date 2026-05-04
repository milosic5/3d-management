<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('filaments.index')" class="hover:underline text-slate-500">{{ $t('filaments.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('filaments.new') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('filaments.new')" description="" />

    <form @submit.prevent="submit" class="max-w-2xl">
      <Card class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.brand') }} *</label>
                <Input v-model="form.brand" required placeholder="e.g. Polymaker" class="w-full" list="brands-list" />
                <datalist id="brands-list">
                    <option v-for="b in brands" :key="b" :value="b"></option>
                </datalist>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.brand">{{ form.errors.brand }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.name') }} *</label>
                <Input v-model="form.name" required placeholder="e.g. PolyLite PETG" class="w-full" list="names-list" />
                <datalist id="names-list">
                    <option v-for="n in names" :key="n" :value="n"></option>
                </datalist>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.material') }} *</label>
                <select v-model="form.material" required class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="pla">PLA</option>
                    <option value="petg">PETG</option>
                </select>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.material">{{ form.errors.material }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.price_per_kg') }} *</label>
                <Input type="number" step="0.01" v-model="form.price_per_kg" required min="0" class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.price_per_kg">{{ form.errors.price_per_kg }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    {{ $t('filaments.empty_spool_weight') }} 
                    <span class="text-slate-400 text-xs font-normal">({{ $t('common.optional') }})</span>
                </label>
                <div class="relative">
                    <Input type="number" step="0.01" v-model="form.empty_spool_weight_grams" min="0" class="w-full pr-8" placeholder="250" />
                    <span class="absolute right-3 top-2.5 text-sm text-slate-400">{{ $t('filaments.empty_spool_weight_unit') }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $t('filaments.empty_spool_weight_helper') }}</p>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.empty_spool_weight_grams">{{ form.errors.empty_spool_weight_grams }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.color_name') }} *</label>
                <Input v-model="form.color_name" required class="w-full" placeholder="e.g. Silk Gold" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.color_name">{{ form.errors.color_name }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.color_hex') }} *</label>
                <ColorPickerGrid v-model="form.color_hex" :title="$t('filaments.color_hex')" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.color_hex">{{ form.errors.color_hex }}</div>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">{{ $t('filaments.notes') }}</label>
                <Textarea v-model="form.notes" rows="3" class="w-full" />
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <Link :href="route('filaments.index')">
                <Button type="button" variant="outline">{{ $t('common.cancel') }}</Button>
            </Link>
            <Button class="bg-orange-500 hover:bg-orange-600 text-white" type="submit" :disabled="form.processing">
                 {{ form.processing ? $t('common.loading') : $t('filaments.new') }}
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
import { Textarea } from '@/Components/ui/textarea'
import ColorPickerGrid from '@/Components/ColorPickerGrid.vue'
import { watch } from 'vue'

const props = defineProps({
    brands: { type: Array, default: () => [] },
    names: { type: Array, default: () => [] }
})

const form = useForm({
    brand: '',
    name: '',
    material: 'pla',
    color_name: '',
    color_hex: '#ffffff',
    price_per_kg: 20.00,
    empty_spool_weight_grams: null,
    notes: ''
})



const submit = () => {
    form.post(route('filaments.store'))
}
</script>
