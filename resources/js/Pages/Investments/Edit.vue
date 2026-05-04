<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('investments.index')" class="hover:underline text-slate-500">{{ $t('investments.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('common.edit', 'Edit') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('common.edit', 'Edit')" description="" />

    <form @submit.prevent="submit" class="max-w-2xl">
      <Card class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">{{ $t('investments.name') }} *</label>
                <Input v-model="form.name" required class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('investments.category') }} *</label>
                <select v-model="form.category_id" required class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option disabled value="">...</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.category_id">{{ form.errors.category_id }}</div>
            </div>

            <div v-if="isFilamentCategory" class="animate-in fade-in slide-in-from-top-1 duration-200">
                <label class="block text-sm font-medium mb-1">Filament</label>
                <select v-if="filaments && filaments.length > 0" v-model="selectedFilamentName" class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="">-- {{ $t('common.select', 'Izaberite') }} --</option>
                    <option v-for="f in filaments" :key="f.name" :value="f.name">{{ f.name }}</option>
                </select>
                <p v-else class="text-xs text-amber-500 mt-1 italic">{{ $t('filaments.no_results', 'Nema pronađenih filamenata') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('investments.invested_at') }} *</label>
                <Input type="date" v-model="form.invested_at" required class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.invested_at">{{ form.errors.invested_at }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('investments.unit_cost', 'Cena po jedinici') }} *</label>
                <Input type="number" step="0.01" v-model="form.unit_cost" required min="0" class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.unit_cost">{{ form.errors.unit_cost }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('investments.quantity') }} *</label>
                <Input type="number" step="1" v-model.number="form.quantity" required min="1" class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.quantity">{{ form.errors.quantity }}</div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">{{ $t('investments.notes') }}</label>
                <Textarea v-model="form.notes" rows="2" class="w-full" />
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <Link :href="route('investments.index')">
                <Button type="button" variant="outline">{{ $t('common.cancel', 'Cancel') }}</Button>
            </Link>
            <Button class="bg-orange-500 hover:bg-orange-600 text-white" type="submit" :disabled="form.processing">
                 {{ form.processing ? $t('common.loading', 'Loading...') : $t('common.save', 'Save') }}
            </Button>
        </div>
      </Card>
    </form>
  </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'

const props = defineProps({ 
    investment: Object, 
    categories: { type: Array, default: () => [] }, 
    filaments: { type: Array, default: () => [] } 
})

const { locale } = useI18n()
const unitLabel = computed(() => locale.value === 'sr' ? 'kom' : 'pcs')

const form = useForm({
    name: props.investment.name,
    category_id: props.investment.category_id,
    invested_at: props.investment.invested_at ? new Date(props.investment.invested_at).toISOString().slice(0, 10) : new Date().toISOString().slice(0, 10),
    unit_cost: props.investment.unit_cost,
    quantity: props.investment.quantity,
    unit: props.investment.unit || unitLabel.value,
    notes: props.investment.notes || ''
})

watch(unitLabel, (val) => { form.unit = val })

const submit = () => {
    form.put(route('investments.update', props.investment.id))
}

const selectedFilamentName = ref('')

const isFilamentCategory = computed(() => {
    if (!form.category_id) return false;
    const cat = props.categories.find(c => String(c.id) == String(form.category_id));
    return cat && (cat.name.toLowerCase().includes('filamen') || cat.id == 2);
});

if (isFilamentCategory.value) {
    const f = props.filaments.find(f => f.name === props.investment.name);
    if (f) {
        selectedFilamentName.value = f.name;
    }
}

watch(selectedFilamentName, (newName) => {
    if (newName) {
        const filament = props.filaments.find(f => f.name === newName);
        if (filament) {
            form.name = filament.name;
            form.unit_cost = filament.price_per_kg;
        }
    }
});

watch(() => form.category_id, (newId) => {
    if (!isFilamentCategory.value) {
        selectedFilamentName.value = '';
    }
});
</script>
