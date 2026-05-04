<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('investments.index')" class="hover:underline text-slate-500">{{ $t('investments.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('investments.categories') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('investments.categories_manage')" description="" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List -->
        <Card class="lg:col-span-2 p-0 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900 border-b dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{{ $t('investments.name') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ $t('investments.categories_type') }}</th>
                        <th class="px-4 py-3 font-semibold text-center">{{ $t('investments.title') }}</th>
                        <th class="px-4 py-3 text-right">{{ $t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-slate-800">
                    <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
                        <td class="px-4 py-3"><span class="capitalize text-xs rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-1">{{ $t('investments.categories_type_' + cat.type) || cat.type }}</span></td>
                        <td class="px-4 py-3 text-center text-slate-500 font-mono">{{ cat.investments_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <Button @click="confirmDelete(cat)" variant="ghost" size="icon" :disabled="cat.investments_count > 0" title="Cannot delete if linked to records">
                                <TrashIcon class="w-4 h-4" :class="cat.investments_count > 0 ? 'text-slate-300' : 'text-red-500'" />
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="!categories.length">
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ $t('common.no_results') }}</td>
                    </tr>
                </tbody>
            </table>
        </Card>

        <!-- Create Form -->
        <div>
            <Card class="p-6 sticky top-24">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">{{ $t('investments.categories_new') }}</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $t('investments.name') }}</label>
                        <Input v-model="form.name" required class="w-full" />
                        <div class="text-red-500 text-sm mt-1" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $t('investments.categories_type') }}</label>
                        <select v-model="form.type" required class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                            <option value="equipment">{{ $t('investments.categories_type_equipment') }}</option>
                            <option value="consumable">{{ $t('investments.categories_type_consumable') }}</option>
                            <option value="utility">{{ $t('investments.categories_type_utility') }}</option>
                            <option value="other">{{ $t('investments.categories_type_other') }}</option>
                        </select>
                    </div>
                    <Button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white" :disabled="form.processing">
                        {{ form.processing ? $t('common.loading') : $t('common.create') }}
                    </Button>
                </form>
            </Card>
        </div>
    </div>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('common.confirm_delete')"
        @confirm="deleteCategory"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { TrashIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useI18n } from 'vue-i18n'

const props = defineProps({ categories: Array })
const { t } = useI18n()

const form = useForm({
    name: '',
    type: 'consumable'
})

const submit = () => {
    form.post(route('investments.categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            toast.success('Category created')
        }
    })
}

const isDeleteDialogOpen = ref(false)
const targetItem = ref(null)

const confirmDelete = (item) => {
    if(item.investments_count > 0) {
        toast.error(t('investments.categories_delete_blocked', { count: item.investments_count }))
        return
    }
    targetItem.value = item
    isDeleteDialogOpen.value = true
}

const deleteCategory = () => {
    if (targetItem.value) {
        router.delete(route('investments.categories.destroy', targetItem.value.id), {
            preserveScroll: true,
            onSuccess: () => isDeleteDialogOpen.value = false
        })
    }
}
</script>
