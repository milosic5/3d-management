import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

export const emptyOrderItem = () => ({
    product_id: '', filament_id: '', quantity: 1, unit_price: 0, print_cost: 0,
    print_time_minutes: 0, weight_grams: 0, color_name: '', color_hex: '', notes: '', _base_unit_cost: 0,
})

/**
 * Value for a datetime-local input in the browser's timezone (now when no date is given).
 */
export const toLocalDateTimeInput = (dateString) => {
    const d = dateString ? new Date(dateString) : new Date()
    const tzoffset = d.getTimezoneOffset() * 60000
    return (new Date(d.getTime() - tzoffset)).toISOString().slice(0, 16)
}

/**
 * Shared state and calculations of the order create/edit form.
 *
 * print_cost is a line total: _base_unit_cost (filament price per kg / 1000 × product weight,
 * or the manually entered cost divided by quantity) multiplied by quantity.
 */
export function useOrderForm(initialData, products, filaments) {
    const { t } = useI18n()

    const form = useForm(initialData)

    if (form.items.length === 0) {
        form.items.push(emptyOrderItem())
    }

    const productOptions = computed(() => products().map(p => ({
        label: `${p.name} (${p.price} ${t('common.currency')})`,
        value: p.id,
    })))

    const filamentOptions = computed(() => filaments().map(f => ({
        label: `${f.brand} - ${f.name} (${f.color_name})`,
        value: f.id,
    })))

    const addItem = () => form.items.push(emptyOrderItem())
    const removeItem = (i) => form.items.splice(i, 1)

    const onProductSelect = (item, val) => {
        const pId = parseInt(val && val.target ? val.target.value : val)
        const matched = products().find(p => p.id === pId)
        if (matched) {
            item.unit_price = matched.price
            item.print_time_minutes = matched.print_time_minutes
            item.weight_grams = matched.weight_grams || 0
            item.color_hex = matched.color_hex
            item.color_name = matched.color_name

            if (item.filament_id) {
                const filament = filaments().find(f => f.id === parseInt(item.filament_id))
                if (filament && matched.weight_grams) {
                    item._base_unit_cost = (filament.price_per_kg / 1000) * matched.weight_grams
                    item.print_cost = parseFloat((item._base_unit_cost * (item.quantity || 1)).toFixed(2))
                }
            }
        }
    }

    const onFilamentSelect = (item, val) => {
        const fId = parseInt(val && val.target ? val.target.value : val)
        if (!fId) {
            item.print_cost = 0
            item._base_unit_cost = 0
            return
        }
        const filament = filaments().find(f => f.id === fId)
        if (item.product_id) {
            const product = products().find(p => p.id === parseInt(item.product_id))
            if (filament && product && product.weight_grams) {
                item._base_unit_cost = (filament.price_per_kg / 1000) * product.weight_grams
                item.print_cost = parseFloat((item._base_unit_cost * (item.quantity || 1)).toFixed(2))
            }
        }
    }

    const onQuantityChange = (item) => {
        if (item._base_unit_cost) {
            item.print_cost = parseFloat((item._base_unit_cost * (item.quantity || 1)).toFixed(2))
        }
    }

    const onManualCostChange = (item) => {
        if (item.quantity && item.quantity > 0) {
            item._base_unit_cost = parseFloat(item.print_cost || 0) / item.quantity
        } else {
            item._base_unit_cost = parseFloat(item.print_cost || 0)
        }
    }

    const computedTotal = computed(() => {
        return form.items.reduce((acc, curr) => acc + (parseFloat(curr.unit_price || 0) * parseInt(curr.quantity || 1)), 0)
    })

    const computedMinutes = computed(() => {
        return form.items.reduce((acc, curr) => acc + parseInt(curr.print_time_minutes || 0), 0)
    })

    return {
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
    }
}
