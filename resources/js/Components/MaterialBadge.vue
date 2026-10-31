<template>
    <div
        v-if="material"
        class="inline-flex items-center rounded-sm border px-2 py-0.5 text-xs font-semibold uppercase tracking-widest"
        :class="known?.color ?? 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'"
    >
        {{ known?.label ?? material }}
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useEnums } from '@/composables/useEnums';

const props = defineProps({
    material: {
        type: String,
        default: null
    }
});

const { material: findMaterial } = useEnums();
const known = computed(() => findMaterial(props.material));
</script>
