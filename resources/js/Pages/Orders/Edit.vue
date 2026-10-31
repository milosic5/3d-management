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

    <OrderForm
        :initial="initial"
        :products="products"
        :filaments="filaments"
        :submit-label="$t('common.save')"
        @submit="form => form.put(route('orders.update', order.id))"
    />
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import OrderForm from '@/Components/OrderForm.vue'
import { toLocalDateTimeInput } from '@/composables/useOrderForm'

const props = defineProps({ order: Object, products: Array, filaments: Array })

const initial = {
    customer_name: props.order.customer_name || 'KP',
    created_at: toLocalDateTimeInput(props.order.created_at),
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
    })) || [],
}
</script>
