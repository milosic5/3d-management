<template>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ title }}</h1>
            <p v-if="description" class="text-sm text-slate-500 mt-1">{{ description }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <slot name="actions"></slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    }
})
</script>
