<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('dashboard')" class="hover:underline text-slate-500">{{ $t('nav.dashboard') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('analytics.breadcrumb') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('analytics.title')" :description="$t('analytics.desc')" />

    <div class="flex flex-col items-center justify-center p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm">
        <BarChart2Icon class="w-16 h-16 text-slate-300 dark:text-slate-700 mb-4" />
        <h2 class="text-xl font-semibold mb-2">{{ $t('analytics.coming_soon') }}</h2>
        <p class="text-slate-500 max-w-md">{{ $t('analytics.coming_soon_desc') }}</p>
        <Link :href="route('dashboard')" class="mt-6">
            <Button variant="outline">{{ $t('analytics.return_btn') }}</Button>
        </Link>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { BarChart2Icon } from 'lucide-vue-next'

defineProps({ placeholder: String })
</script>
