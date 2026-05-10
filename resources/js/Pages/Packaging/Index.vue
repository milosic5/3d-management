<template>
  <AppLayout>
    <template #breadcrumb><span class="text-slate-500 font-medium">{{ $t('packagings.title') }}</span></template>
    
    <PageHeader :title="$t('packagings.title')" description="">
      <template #actions>
        <div class="flex space-x-2">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white" @click="openModal('box')"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('packagings.new_box') }}</Button>
            <Button class="bg-blue-500 hover:bg-blue-600 text-white" @click="openModal('envelope')"><PlusIcon class="w-4 h-4 mr-2" /> {{ $t('packagings.new_envelope') }}</Button>
        </div>
      </template>
    </PageHeader>
    
    <div class="mb-4 border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="activeTab = 'box'" :class="[activeTab === 'box' ? 'border-orange-500 text-orange-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                {{ $t('packagings.boxes') }}
            </button>
            <button @click="activeTab = 'envelope'" :class="[activeTab === 'envelope' ? 'border-orange-500 text-orange-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                {{ $t('packagings.envelopes') }}
            </button>
        </nav>
    </div>

    <DataTable :data="filteredData" :columns="columns" :page-size="15" searchable :search-placeholder="$t('packagings.search_placeholder')" />

    <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-slate-900 mb-4">
                {{ form.id ? $t('packagings.edit') : (form.type === 'box' ? $t('packagings.new_box') : $t('packagings.new_envelope')) }}
            </h2>
            
            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <div>
                        <InputLabel :value="$t('packagings.name')" />
                        <TextInput v-model="form.name" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4" :class="{'grid-cols-3': form.type === 'box'}">
                        <div>
                            <InputLabel :value="$t('packagings.length')" />
                            <TextInput v-model="form.length" type="number" step="0.1" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.length" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel :value="$t('packagings.width')" />
                            <TextInput v-model="form.width" type="number" step="0.1" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.width" class="mt-2" />
                        </div>
                        <div v-if="form.type === 'box'">
                            <InputLabel :value="$t('packagings.height')" />
                            <TextInput v-model="form.height" type="number" step="0.1" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.height" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel :value="$t('packagings.stock')" />
                        <TextInput v-model="form.stock" type="number" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.stock" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">{{ $t('common.cancel') }}</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ $t('common.save') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>

    <ConfirmDialog 
        v-model:is-open="isDeleteDialogOpen"
        :title="$t('common.confirm_delete')"
        :description="$t('packagings.delete_confirm')"
        @confirm="deleteItem"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import DataTable from '@/Components/DataTable.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Button } from '@/Components/ui/button'
import { PlusIcon, PencilIcon, TrashIcon, MinusIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useI18n } from 'vue-i18n'

const props = defineProps({ packagings: Array })
const { t } = useI18n()

const activeTab = ref('box')

const filteredData = computed(() => {
    return props.packagings.filter(p => p.type === activeTab.value)
})

const isModalOpen = ref(false)
const isDeleteDialogOpen = ref(false)
const targetItem = ref(null)

const form = useForm({
    id: null,
    type: 'box',
    name: '',
    length: '',
    width: '',
    height: '',
    stock: 0,
})

const openModal = (type, item = null) => {
    form.clearErrors()
    if (item) {
        form.id = item.id
        form.type = item.type
        form.name = item.name || ''
        form.length = item.length
        form.width = item.width
        form.height = item.height || ''
        form.stock = item.stock
    } else {
        form.reset()
        form.type = type
        form.stock = 0
    }
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
    form.reset()
}

const submitForm = () => {
    if (form.id) {
        form.put(route('packagings.update', form.id), {
            onSuccess: () => {
                closeModal()
                toast.success(t('common.save') + ' OK')
            }
        })
    } else {
        form.post(route('packagings.store'), {
            onSuccess: () => {
                closeModal()
                toast.success(t('common.save') + ' OK')
            }
        })
    }
}

const confirmDelete = (item) => {
    targetItem.value = item
    isDeleteDialogOpen.value = true
}

const deleteItem = () => {
    if (targetItem.value) {
        router.delete(route('packagings.destroy', targetItem.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isDeleteDialogOpen.value = false
                toast.success(t('packagings.deleted'))
            }
        })
    }
}

const addStock = (item) => {
    router.post(route('packagings.add-stock', item.id), {}, {
        preserveScroll: true
    })
}

const removeStock = (item) => {
    router.post(route('packagings.remove-stock', item.id), {}, {
        preserveScroll: true
    })
}

const columns = computed(() => {
  return [
    { header: t('packagings.name'), accessorKey: 'name', cell: ({ row }) => h('span', { class: 'font-semibold' }, row.original.name || '-') },
    {
      header: t('packagings.dimensions'),
      id: 'dimensions',
      cell: ({ row }) => {
          const r = row.original
          const fmt = (val) => Number(val)
          if (r.type === 'box') {
              return `${fmt(r.length)} x ${fmt(r.width)} x ${fmt(r.height)} cm`
          }
          return `${fmt(r.length)} x ${fmt(r.width)} cm`
      }
    },
    {
      header: t('packagings.stock'),
      id: 'stock',
      cell: ({ row }) => {
          return h('div', { class: 'flex items-center space-x-2' }, [
              h(Button, { variant: 'outline', size: 'icon', class: 'w-6 h-6', onClick: () => removeStock(row.original) }, () => h(MinusIcon, { class: 'w-3 h-3' })),
              h('span', { class: 'font-mono font-medium w-8 text-center inline-block' }, row.original.stock),
              h(Button, { variant: 'outline', size: 'icon', class: 'w-6 h-6', onClick: () => addStock(row.original) }, () => h(PlusIcon, { class: 'w-3 h-3' }))
          ])
      }
    },
    {
      id: 'actions',
      cell: ({ row }) => h('div', { class: 'flex items-center space-x-2' }, [
          h(Button, { variant: 'ghost', size: 'icon', onClick: () => openModal(row.original.type, row.original) }, () => h(PencilIcon, { class: 'w-4 h-4 text-slate-500' })),
          h(Button, { variant: 'ghost', size: 'icon', onClick: () => confirmDelete(row.original) }, () => h(TrashIcon, { class: 'w-4 h-4 text-red-500' }))
      ])
    }
  ]
})
</script>
