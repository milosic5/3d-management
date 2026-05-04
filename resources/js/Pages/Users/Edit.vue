<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('users.index')" class="hover:underline text-slate-500">{{ $t('users.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-500">{{ user.name }}</span>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('users.edit') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('users.edit')" description="" />

    <form @submit.prevent="submit" class="max-w-2xl">
      <Card class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('users.name') }} *</label>
                <Input v-model="form.name" required class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('users.email') }} *</label>
                <Input type="email" v-model="form.email" required class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.email">{{ form.errors.email }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('users.password') }}</label>
                <Input type="password" v-model="form.password" class="w-full" :placeholder="$t('users.password_hint')" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.password">{{ form.errors.password }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('users.role') }} *</label>
                <select v-model="form.role" required class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-visible:ring-slate-300">
                    <option value="operator">{{ $t('users.role_operator') }}</option>
                    <option value="admin">{{ $t('users.role_admin') }}</option>
                </select>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.role">{{ form.errors.role }}</div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <Link :href="route('users.index')">
                <Button type="button" variant="outline">{{ $t('common.cancel') }}</Button>
            </Link>
            <Button class="bg-orange-500 hover:bg-orange-600 text-white" type="submit" :disabled="form.processing">
                 {{ form.processing ? $t('common.loading') : $t('common.save') }}
            </Button>
        </div>
      </Card>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'

const props = defineProps({ user: Object })

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    password: '',
    role: props.user.role
})

const submit = () => {
    form.post(route('users.update', props.user.id))
}
</script>
