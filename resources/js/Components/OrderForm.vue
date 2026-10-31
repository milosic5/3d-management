<template>
    <form @submit.prevent="emit('submit', form)" class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
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
                    <option v-for="s in orderStatuses" :key="s.value" :value="s.value">{{ $t('status.' + s.value) }}</option>
                </select>
            </div>

            <Button type="submit" class="w-full mt-6 bg-slate-900 text-white hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700" :disabled="form.processing">
                {{ form.processing ? $t('common.loading') : submitLabel }}
            </Button>
        </Card>
      </div>
    </form>
</template>

<script setup>
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { PlusIcon, TrashIcon } from 'lucide-vue-next'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import { useOrderForm } from '@/composables/useOrderForm'
import { useEnums } from '@/composables/useEnums'

const props = defineProps({
    /** Initial form data built by the page (new, duplicated or existing order). */
    initial: { type: Object, required: true },
    products: { type: Array, required: true },
    filaments: { type: Array, required: true },
    submitLabel: { type: String, required: true },
})

/** Emits the Inertia form; the page decides whether to post or put. */
const emit = defineEmits(['submit'])

const { orderStatuses } = useEnums()

const {
    form,
    productOptions,
    filamentOptions,
    addItem,
    removeItem,
    onProductSelect,
    onFilamentSelect,
    onQuantityChange,
    onManualCostChange,
    computedTotal,
    computedMinutes,
} = useOrderForm(props.initial, () => props.products, () => props.filaments)
</script>
