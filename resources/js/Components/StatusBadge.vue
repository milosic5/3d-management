<template>
    <div
        class="inline-flex items-center rounded-full border border-transparent px-2.5 py-0.5 text-xs font-semibold uppercase font-mono tracking-wider transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
        :class="statusColor(status) || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'"
    >
        {{ $t(`status.${status}`) }}
    </div>
</template>

<script setup>
import { useEnums } from '@/composables/useEnums';

defineProps({
    status: {
        type: String,
        required: true
    }
});

const { statusColor } = useEnums();
</script>
