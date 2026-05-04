<template>
    <div 
        class="inline-flex items-center rounded-sm border px-2 py-0.5 text-xs font-semibold uppercase tracking-widest"
        :class="badgeClasses"
    >
        {{ material }}
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    material: {
        type: String,
        required: true
    }
});

const badgeClasses = computed(() => {
    switch (props.material?.toLowerCase()) {
        case 'pla': return 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-950 dark:text-orange-400 dark:border-orange-900';
        case 'petg': return 'bg-violet-100 text-violet-800 border-violet-200 dark:bg-violet-950 dark:text-violet-400 dark:border-violet-900';
        default: return 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
    }
});
</script>
