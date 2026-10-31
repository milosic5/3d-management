<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('orders.index')" class="hover:underline text-slate-500">{{ $t('orders.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('orders.new') }}</span>
        </div>
    </template>

    <PageHeader :title="$t('orders.new')" description="" />

    <OrderForm
        :initial="initial"
        :products="products"
        :filaments="filaments"
        :submit-label="$t('orders.create_order')"
        @submit="form => form.post(route('orders.store'))"
    />
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import OrderForm from '@/Components/OrderForm.vue'
import { toLocalDateTimeInput } from '@/composables/useOrderForm'

const props = defineProps({ products: Array, filaments: Array, duplicateOrder: Object })

// A new order, optionally pre-filled from the order being duplicated.
const duplicate = props.duplicateOrder

const initial = {
    customer_name: duplicate ? duplicate.customer_name : 'KP',
    created_at: toLocalDateTimeInput(),
    notes: duplicate ? duplicate.notes : '',
    status: 'received',
    estimated_print_minutes: duplicate ? duplicate.estimated_print_minutes : null,
    items: duplicate?.items?.length > 0 ? duplicate.items.map(i => ({
        product_id: i.product_id,
        filament_id: i.filament_id || '',
        quantity: i.quantity,
        unit_price: i.unit_price,
        print_cost: i.print_cost || 0,
        print_time_minutes: i.print_time_minutes,
        weight_grams: i.weight_grams,
        color_name: i.color_name || '',
        color_hex: i.color_hex || '',
        notes: i.notes || '',
        _base_unit_cost: i.quantity ? ((i.print_cost || 0) / i.quantity) : 0
    })) : [],
}
</script>
