<template>
    <div class="flex items-center gap-2">
        <div 
            class="h-4 w-4 rounded-full border border-gray-200 dark:border-gray-700 shadow-inner flex-shrink-0"
            :style="{ backgroundColor: colorHex }"
        ></div>
        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate max-w-[120px]" v-if="colorName">
            {{ colorName }}
        </span>
    </div>
</template>

<script setup>
defineProps({
    colorHex: { type: String, required: true },
    colorName: { type: String, default: '' },
})
</script>
