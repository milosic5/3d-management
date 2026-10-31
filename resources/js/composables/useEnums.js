import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Order statuses and filament materials shared by the backend enums
 * (App\Enums\OrderStatus, App\Enums\FilamentMaterial) through Inertia.
 * Status labels come from i18n ("status.<value>").
 */
export function useEnums() {
    const page = usePage()

    const orderStatuses = computed(() => page.props.enums?.orderStatuses ?? [])
    const materials = computed(() => page.props.enums?.materials ?? [])

    const statusColor = (value) => orderStatuses.value.find(s => s.value === value)?.color ?? ''
    const material = (value) => materials.value.find(m => m.value === value?.toLowerCase())

    return { orderStatuses, materials, statusColor, material }
}
