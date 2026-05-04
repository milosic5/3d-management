<template>
    <div class="relative group p-6 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-center cursor-pointer">
        <input 
            type="file" 
            :accept="accept" 
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
            @change="$emit('change', $event)"
        >
        <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
            <slot name="icon" class="text-gray-400 dark:text-gray-500"></slot>
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ label || $t('common.upload_hint') }}
            </div>
            <div v-if="hint" class="text-xs text-gray-500 dark:text-gray-400 max-w-[200px] text-center">
                {{ hint }}
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    accept: { type: String, default: '*/*' },
    label: { type: String, default: '' },
    hint: { type: String, default: '' },
})
</script>
