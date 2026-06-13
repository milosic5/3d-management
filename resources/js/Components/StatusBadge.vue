<template>
    <div 
        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold uppercase font-mono tracking-wider transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
        :class="badgeClasses"
    >
        {{ $t(`status.${status}`) }}
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    }
});

const badgeClasses = computed(() => {
    switch (props.status) {
        case 'received': return 'border-transparent bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'printing': return 'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 shadow-sm shadow-amber-500/20';
        case 'finished': return 'border-transparent bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 shadow-sm shadow-green-500/20';
        case 'delivered': return 'border-transparent bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300';
        case 'cancelled': return 'border-transparent bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'border-transparent bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    }
});
</script>
