<template>
  <AppLayout>
    <template #breadcrumb>
        <span class="text-slate-500 font-medium">{{ $t('users.title') }}</span>
    </template>
    
    <PageHeader :title="$t('users.title')" description="">
      <template #actions>
        <Link :href="route('users.create')">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white"><UserPlusIcon class="w-4 h-4 mr-2" /> {{ $t('users.new') }}</Button>
        </Link>
      </template>
    </PageHeader>
    
    <DataTable :data="users.data" :columns="columns" :page-size="15">
        <template #empty>
            <div class="flex flex-col items-center justify-center py-8 text-slate-500">
                <UsersIcon class="w-12 h-12 mb-3 opacity-20" />
                <p>{{ $t('common.no_results') }}</p>
            </div>
        </template>
    </DataTable>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('users.delete_confirm')"
        @confirm="deleteUser"
    />
  </AppLayout>
</template>

<script setup>
import { ref, h } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { PencilIcon, TrashIcon, UserPlusIcon, ShieldIcon, UserIcon, UsersIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'

import { useI18n } from 'vue-i18n'

const props = defineProps({ users: Object })
const page = usePage()
const { t } = useI18n()

const isDeleteDialogOpen = ref(false)
const targetItem = ref(null)

const confirmDelete = (item) => {
    if(item.id === page.props.auth.user.id) {
        toast.error(t('users.delete_blocked_self'));
        return;
    }
    targetItem.value = item;
    isDeleteDialogOpen.value = true;
}

const deleteUser = () => {
    if (targetItem.value) {
        router.delete(route('users.destroy', targetItem.value.id), {
            preserveScroll: true,
            onError: () => {
                if(page.props.flash.error) toast.error(page.props.flash.error)
            },
            onSuccess: () => {
                isDeleteDialogOpen.value = false
                toast.success('User access revoked')
            }
        })
    }
}

const columns = [
  { header: t('users.name'), accessorKey: 'name', cell: ({ row }) => h('span', { class: 'font-semibold' }, row.original.name) },
  { header: t('users.email'), accessorKey: 'email' },
  {
    header: t('users.role'),
    cell: ({ row }) => h('div', { class: ['inline-flex items-center space-x-1 border px-2 py-0.5 rounded-full text-xs font-semibold uppercase', row.original.role === 'admin' ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800' : 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'] }, [
        h(row.original.role === 'admin' ? ShieldIcon : UserIcon, { class: 'w-3 h-3' }),
        h('span', t('users.role_' + row.original.role))
    ])
  },
  { header: t('users.created_at'), cell: ({ row }) => new Date(row.original.created_at).toLocaleDateString() },
  {
    id: 'actions',
    cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
        h(Link, { href: route('users.edit', row.original.id) }, () => h(Button, { variant: 'ghost', size: 'icon' }, () => h(PencilIcon, { class: 'w-4 h-4 text-slate-500' }))),
        h(Button, { variant: 'ghost', size: 'icon', onClick: () => confirmDelete(row.original) }, () => h(TrashIcon, { class: 'w-4 h-4 text-red-500' }))
    ])
  }
]
</script>
