<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('orders.index')" class="hover:underline text-slate-500">{{ $t('orders.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ order.order_number }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('common.edit') + ' ' + order.order_number" description="" />

    <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
      <!-- Main Form Area -->
      <div class="xl:col-span-2 space-y-6">
        <!-- Customer Info -->
        <Card class="p-6">
            <h3 class="text-lg font-semibold mb-4">{{ $t('orders.customer_details') }}</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $t('orders.customer_name') }} *</label>
                        <Input v-model="form.customer_name" required class="w-full" />
                        <div class="text-red-500 text-sm mt-1" v-if="form.errors.customer_name">{{ form.errors.customer_name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $t('orders.creation_date') }}</label>
                        <Input type="datetime-local" v-model="form.created_at" class="w-full" />
                        <div class="text-red-500 text-sm mt-1" v-if="form.errors.created_at">{{ form.errors.created_at }}</div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $t('orders.order_notes') }}</label>
                    <Textarea v-model="form.notes" rows="3" class="w-full" />
                </div>
            </div>
        </Card>

        <!-- Dynamic Line Items -->
        <Card class="p-0 overflow-visible">
            <div class="p-6 border-b flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50 rounded-t-xl">
                <h3 class="text-lg font-semibold">{{ $t('orders.order_items') }}</h3>
                <Button type="button" variant="outline" size="sm" @click="addItem"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('orders.add_item') }}</Button>
            </div>
            
            <div class="divide-y">
                <div v-for="(item, i) in form.items" :key="i" class="p-6 last:rounded-b-xl">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-slate-700 dark:text-slate-300">{{ $t('orders.line_item') }} {{ i + 1 }}</h4>
                        <Button v-if="form.items.length > 1" type="button" variant="ghost" size="icon" @click="removeItem(i)" class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <TrashIcon class="w-4 h-4" />
                        </Button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-medium mb-1">{{ $t('orders.product') }} *</label>
                            <SearchableSelect 
                                v-model="item.product_id" 
                                :options="productOptions" 
                                :placeholder="$t('orders.select_product')" 
                                :searchPlaceholder="$t('common.search', 'Search...')"
                                @change="onProductSelect(item, $event)"
                                :error="!!form.errors[`items.${i}.product_id`]"
                            />
                            <div class="text-red-500 text-sm mt-1" v-if="form.errors[`items.${i}.product_id`]">{{ form.errors[`items.${i}.product_id`] }}</div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium mb-1">{{ $t('orders.filament') }}</label>
                            <SearchableSelect 
                                v-model="item.filament_id" 
                                :options="filamentOptions" 
                                :placeholder="$t('orders.select_filament')" 
                                :searchPlaceholder="$t('common.search', 'Search...')"
                                @change="onFilamentSelect(item, $event)"
                                :error="!!form.errors[`items.${i}.filament_id`]"
                            />
                            <div class="text-red-500 text-sm mt-1" v-if="form.errors[`items.${i}.filament_id`]">{{ form.errors[`items.${i}.filament_id`] }}</div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium mb-1">{{ $t('orders.qty') }} *</label>
                            <Input type="number" v-model="item.quantity" @input="onQuantityChange(item)" required min="1" class="w-full" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-10 gap-4 mt-4">
                        <div class="md:col-span-2 block">
                            <label class="block text-xs font-medium mb-1">{{ $t('orders.print_cost') }}</label>
                            <Input type="number" step="0.01" v-model="item.print_cost" @change="onManualCostChange(item)" class="w-full bg-slate-50 dark:bg-slate-800" />
                            <div class="text-red-500 text-sm mt-1" v-if="form.errors[`items.${i}.print_cost`]">{{ form.errors[`items.${i}.print_cost`] }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">{{ $t('products.print_time_col') }} (min) *</label>
                            <Input type="number" v-model="item.print_time_minutes" required min="0" class="w-full" />
                            <div class="text-red-500 text-sm mt-1" v-if="form.errors[`items.${i}.print_time_minutes`]">{{ form.errors[`items.${i}.print_time_minutes`] }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">{{ $t('products.weight_col') }}</label>
                            <Input type="number" step="0.01" v-model="item.weight_grams" min="0" class="w-full" />
                            <div class="text-red-500 text-sm mt-1" v-if="form.errors[`items.${i}.weight_grams`]">{{ form.errors[`items.${i}.weight_grams`] }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">{{ $t('orders.unit_price') }} *</label>
                            <Input type="number" step="0.01" v-model="item.unit_price" required min="0" class="w-full" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">{{ $t('orders.line_total') }}</label>
                            <div class="h-10 px-3 py-2 flex items-center bg-transparent border border-transparent font-mono font-bold text-orange-600">
                                {{ (item.quantity * item.unit_price).toFixed(2) }} {{ $t('common.currency') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
      </div>
      
      <!-- Summary Panel -->
      <div class="xl:col-span-1 space-y-6 sticky top-24">
        <Card class="p-6">
            <h3 class="text-lg font-semibold mb-4">{{ $t('orders.summary') }}</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">{{ $t('orders.items_count_label') }}</span>
                    <span class="font-medium">{{ form.items.length }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">{{ $t('orders.est_print_time') }}</span>
                    <span class="font-medium">{{ Math.floor(computedMinutes / 60) }}h {{ computedMinutes % 60 }}m</span>
                </div>
                <div class="border-t pt-4 mt-2 mb-2"></div>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold">{{ $t('orders.total_price_label') }}</span>
                    <span class="text-2xl font-mono font-bold text-orange-500">{{ computedTotal.toFixed(2) }} {{ $t('common.currency') }}</span>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium mb-1">{{ $t('orders.initial_status') }}</label>
                <select v-model="form.status" class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="received">{{ $t('status.received') }}</option>
                    <option value="printing">{{ $t('status.printing') }}</option>
                    <option value="finished">{{ $t('status.finished') }}</option>
                    <option value="delivered">{{ $t('status.delivered') }}</option>
                    <option value="cancelled">{{ $t('status.cancelled') }}</option>
                </select>
            </div>
            
            <Button type="submit" class="w-full mt-6 bg-slate-900 text-white hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700" :disabled="form.processing">
                {{ form.processing ? $t('common.loading') : $t('common.save') }}
            </Button>
        </Card>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { PlusIcon, TrashIcon } from 'lucide-vue-next'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({ order: Object, products: Array, filaments: Array })
const { t } = useI18n()

const productOptions = computed(() => {
    return props.products.map(p => ({
        label: `${p.name} (${p.price} ${t('common.currency')})`,
        value: p.id
    }))
})

const filamentOptions = computed(() => {
    return props.filaments.map(f => ({
        label: `${f.brand} - ${f.name} (${f.color_name})`,
        value: f.id
    }))
})

const getLocalISOString = (dateString) => {
    const d = dateString ? new Date(dateString) : new Date();
    const tzoffset = d.getTimezoneOffset() * 60000;
    return (new Date(d.getTime() - tzoffset)).toISOString().slice(0, 16);
}

const form = useForm({
    customer_name: props.order.customer_name || 'KP',
    created_at: getLocalISOString(props.order.created_at),
    notes: props.order.notes || '',
    status: props.order.status || 'received',
    estimated_print_minutes: props.order.estimated_print_minutes || null,
    items: props.order.items?.map(i => ({
        product_id: i.product_id,
        filament_id: i.filament_id || '',
        quantity: i.quantity,
        unit_price: parseFloat(i.unit_price) || 0,
        print_cost: parseFloat(i.print_cost) || 0,
        print_time_minutes: parseInt(i.print_time_minutes) || 0,
        weight_grams: parseFloat(i.weight_grams) || 0,
        color_name: i.color_name || '',
        color_hex: i.color_hex || '',
        notes: i.notes || '',
        _base_unit_cost: i.quantity > 0 ? (parseFloat(i.print_cost) || 0) / i.quantity : 0
    })) || []
})

if (form.items.length === 0) {
    form.items.push({ product_id: '', filament_id: '', quantity: 1, unit_price: 0, print_cost: 0, print_time_minutes: 0, weight_grams: 0, color_name: '', color_hex: '', notes: '', _base_unit_cost: 0 })
}

const addItem = () => {
    form.items.push({ product_id: '', filament_id: '', quantity: 1, unit_price: 0, print_cost: 0, print_time_minutes: 0, weight_grams: 0, color_name: '', color_hex: '', notes: '', _base_unit_cost: 0 })
}
const removeItem = (i) => form.items.splice(i, 1)

const onProductSelect = (item, val) => {
    const pId = parseInt(val && val.target ? val.target.value : val)
    const matched = props.products.find(p => p.id === pId)
    if (matched) {
        item.unit_price = matched.price;
        item.print_time_minutes = matched.print_time_minutes;
        item.weight_grams = matched.weight_grams || 0;
        item.color_hex = matched.color_hex;
        item.color_name = matched.color_name;
        
        if (item.filament_id) {
            const filament = props.filaments.find(f => f.id === parseInt(item.filament_id))
            if (filament && matched.weight_grams) {
                item._base_unit_cost = (filament.price_per_kg / 1000) * matched.weight_grams;
                item.print_cost = parseFloat((item._base_unit_cost * (item.quantity || 1)).toFixed(2));
            }
        }
    }
}

const onFilamentSelect = (item, val) => {
    const fId = parseInt(val && val.target ? val.target.value : val)
    if (!fId) {
        item.print_cost = 0;
        item._base_unit_cost = 0;
        return;
    }
    const filament = props.filaments.find(f => f.id === fId)
    if (item.product_id) {
        const product = props.products.find(p => p.id === parseInt(item.product_id))
        if (filament && product && product.weight_grams) {
            item._base_unit_cost = (filament.price_per_kg / 1000) * product.weight_grams;
            item.print_cost = parseFloat((item._base_unit_cost * (item.quantity || 1)).toFixed(2));
        }
    }
}

const onQuantityChange = (item) => {
    if (item._base_unit_cost) {
        item.print_cost = parseFloat((item._base_unit_cost * (item.quantity || 1)).toFixed(2));
    }
}

const onManualCostChange = (item) => {
    if (item.quantity && item.quantity > 0) {
        item._base_unit_cost = parseFloat(item.print_cost || 0) / item.quantity;
    } else {
        item._base_unit_cost = parseFloat(item.print_cost || 0);
    }
}

const computedTotal = computed(() => {
    return form.items.reduce((acc, curr) => acc + (parseFloat(curr.unit_price || 0) * parseInt(curr.quantity || 1)), 0)
})

const computedMinutes = computed(() => {
    return form.items.reduce((acc, curr) => {
        return acc + parseInt(curr.print_time_minutes || 0)
    }, 0)
})

const submit = () => form.put(route('orders.update', props.order.id))
</script>
