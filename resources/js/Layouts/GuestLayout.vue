<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#F8F9FB] dark:bg-slate-950 text-slate-900 dark:text-slate-100">
        <div>
            <Link href="/">
                <div class="flex items-center justify-center mb-6 text-[#F97316]">
                    <LayersIcon class="w-12 h-12" />
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">3D PrintShop</h1>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white dark:bg-slate-900 shadow-xl sm:rounded-xl border border-gray-100 dark:border-slate-800">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { LayersIcon } from 'lucide-vue-next';
</script>
