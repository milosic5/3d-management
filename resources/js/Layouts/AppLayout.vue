<template>
    <div class="min-h-screen bg-[#F8F9FB] dark:bg-[#0F1117] text-slate-900 dark:text-slate-100 flex font-sans">
        
        <!-- Mobile Sidebar Backdrop -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 z-10 bg-slate-900/50 backdrop-blur-sm md:hidden"></div>

        <!-- Sidebar -->
        <aside :class="[
            'w-[240px] bg-[#0F1117] text-slate-300 flex-shrink-0 fixed h-full z-20 flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0',
            isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]">
            <!-- Brand -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
                <div class="flex items-center">
                    <LayersIcon class="w-6 h-6 text-orange-500 mr-3" />
                    <span class="font-bold text-white tracking-tight">3D PrintShop</span>
                </div>
                <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                    <XIcon class="w-5 h-5" />
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <Link :href="route('dashboard')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('dashboard') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <LayoutDashboardIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.dashboard') }}</span>
                </Link>
                <Link :href="route('orders.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('orders.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <ClipboardListIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.orders') }}</span>
                </Link>
                <Link :href="route('products.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('products.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <BoxIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.products') }}</span>
                </Link>
                <Link :href="route('packagings.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('packagings.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <PackageIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.packagings') }}</span>
                </Link>
                <Link :href="route('filaments.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('filaments.index') || route().current('filaments.create') || route().current('filaments.edit') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <LayersIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.filaments') }}</span>
                </Link>
                <Link :href="route('filaments.stock')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('filaments.stock') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <PackagePlusIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.filament_stock') || 'Filament Stock' }}</span>
                </Link>
                <Link :href="route('printers.index')" class="flex items-center justify-between px-3 py-2 rounded-md transition-colors" :class="route().current('printers.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <div class="flex items-center">
                        <PrinterIcon class="w-5 h-5 mr-3" />
                        <span>{{ $t('nav.printers') }}</span>
                    </div>
                    <div v-if="$page.props.auth.hasPrinterNotifications" class="w-2 h-2 bg-red-500 rounded-full"></div>
                </Link>
                <Link :href="route('calibrations.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('calibrations.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                    <SlidersHorizontalIcon class="w-5 h-5 mr-3" />
                    <span>{{ $t('nav.calibrations') }}</span>
                </Link>

                <template v-if="$page.props.auth.user.role === 'admin'">
                    <div class="pt-6 pb-2 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('nav.admin') }}</div>
                    
                    <Link :href="route('investments.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('investments.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                        <TrendingUpIcon class="w-5 h-5 mr-3" />
                        <span>{{ $t('nav.investments') }}</span>
                    </Link>
                    <Link :href="route('analytics.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('analytics.index') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                        <BarChart2Icon class="w-5 h-5 mr-3" />
                        <span>{{ $t('nav.analytics') }}</span>
                    </Link>
                    <Link :href="route('users.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('users.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                        <UsersIcon class="w-5 h-5 mr-3" />
                        <span>{{ $t('nav.users') }}</span>
                    </Link>
                    <Link :href="route('settings.index')" class="flex items-center px-3 py-2 rounded-md transition-colors" :class="route().current('settings.*') ? 'bg-orange-500/10 text-orange-500' : 'hover:bg-slate-800 hover:text-white'">
                        <SettingsIcon class="w-5 h-5 mr-3" />
                        <span>{{ $t('nav.settings') }}</span>
                    </Link>
                </template>
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center w-full">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-white mr-3">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $page.props.auth.user.role }}</div>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="mt-3 w-full text-left text-sm text-slate-400 hover:text-white flex items-center px-2 py-1.5 rounded bg-slate-800/50 hover:bg-slate-700 transition-colors">
                    <LogOutIcon class="w-4 h-4 mr-2" /> {{ $t('common.logout') }}
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-h-screen md:ml-[240px] w-full md:w-auto">
            <!-- Topbar -->
            <header class="h-16 bg-white dark:bg-[#0F1117] border-b border-gray-200 dark:border-slate-800 flex items-center justify-between px-4 md:px-6 z-10 sticky top-0">
                <div class="flex items-center">
                    <!-- Hamburger Menu -->
                    <button @click="isSidebarOpen = true" class="mr-4 md:hidden text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        <MenuIcon class="w-6 h-6" />
                    </button>
                    <!-- Breadcrumbs placeholder slot -->
                    <div class="hidden sm:flex items-center text-sm text-slate-500">
                        <slot name="breadcrumb" />
                    </div>
                </div>
                
                <!-- Right actions -->
                <div class="flex items-center space-x-4">
                    <!-- Language Toggle -->
                    <button @click="toggleLocale" class="flex items-center text-slate-500 hover:text-slate-700 transition-colors">
                        <GlobeIcon class="w-5 h-5 mr-1" />
                        <span class="text-sm font-medium uppercase">{{ $i18n.locale }}</span>
                    </button>
                    <!-- Toaster -->
                    <Toaster position="top-right" />
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6 flex-1">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Toaster } from 'vue-sonner';
import { 
    LayoutDashboardIcon, ClipboardListIcon, BoxIcon, 
    LayersIcon, SlidersHorizontalIcon, TrendingUpIcon, BarChart2Icon, 
    UsersIcon, SettingsIcon, LogOutIcon, GlobeIcon, PrinterIcon,
    MenuIcon, XIcon, PackageIcon, PackagePlusIcon
} from 'lucide-vue-next';

const { locale } = useI18n();
const isSidebarOpen = ref(false);

const toggleLocale = () => {
    const newLocale = locale.value === 'en' ? 'sr' : 'en';
    locale.value = newLocale;
    localStorage.setItem('locale', newLocale);
    
    // Sync backend locale if Route exists
    router.post(route('locale.update'), { locale: newLocale }, { preserveScroll: true });
};
</script>
