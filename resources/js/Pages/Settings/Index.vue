<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('settings.title') }}</span></template>
    
    <PageHeader :title="$t('settings.title')" description="" />

    <form @submit.prevent="submit" class="max-w-2xl">
      <Card class="p-6 space-y-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('settings.app_name') }}</label>
                <Input v-model="form.app_name" class="w-full" :placeholder="$t('settings.app_name_hint')" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.app_name">{{ form.errors.app_name }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('settings.default_locale') }}</label>
                <select v-model="form.default_locale" class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="en">English (US)</option>
                    <option value="sr">Serbian / Srpski</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">{{ $t('settings.default_locale_hint') }}</p>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.default_locale">{{ form.errors.default_locale }}</div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white" type="submit" :disabled="form.processing">
                 {{ form.processing ? $t('common.loading') : $t('settings.save') }}
            </Button>
        </div>
      </Card>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { toast } from 'vue-sonner'

import { useI18n } from 'vue-i18n'

const props = defineProps({ settings: Object })
const { t } = useI18n()

const form = useForm({
    app_name: props.settings.app_name || '3D PrintShop',
    default_locale: props.settings.default_locale || 'en'
})

const submit = () => {
    form.post(route('settings.store'), {
        preserveScroll: true,
        onSuccess: () => {
            const page = usePage()
            if(page.props.flash?.success) {
                toast.success(page.props.flash.success)
            } else {
                toast.success(t('settings.saved'))
            }
        }
    })
}
</script>
