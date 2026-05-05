<template>
  <AppLayout>
    <template #breadcrumb>
        <div class="flex items-center space-x-2">
            <Link :href="route('products.index')" class="hover:underline text-slate-500">{{ $t('products.title') }}</Link>
            <span class="text-slate-400">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $t('products.new') }}</span>
        </div>
    </template>
    
    <PageHeader :title="$t('products.new')" description="" />

    <form @submit.prevent="submit" class="max-w-4xl space-y-6">
      <Card class="p-6">
        <h3 class="text-lg font-semibold mb-4">{{ $t('products.general_info') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">{{ $t('products.name') }} *</label>
                <Input v-model="form.name" required class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">{{ $t('products.description') }}</label>
                <Textarea v-model="form.description" rows="3" class="w-full" />
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('products.price') }} *</label>
                <Input type="number" step="0.01" v-model="form.price" required min="0" class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.price">{{ form.errors.price }}</div>
            </div>
            
            <div class="flex items-center h-full pt-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500 w-5 h-5 mr-3">
                    <span class="text-sm font-medium">{{ $t('products.is_active') }}</span>
                </label>
            </div>
        </div>
      </Card>

      <Card class="p-6">
        <h3 class="text-lg font-semibold mb-4">{{ $t('products.print_variables') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('products.weight_grams') }} *</label>
                <Input type="number" step="0.01" v-model="form.weight_grams" required min="0" class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.weight_grams">{{ form.errors.weight_grams }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('products.print_time_minutes') }} *</label>
                <Input type="number" v-model="form.print_time_minutes" required min="1" class="w-full" />
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.print_time_minutes">{{ form.errors.print_time_minutes }}</div>
            </div>
        </div>
      </Card>

      <Card class="p-6">
        <h3 class="text-lg font-semibold mb-4">{{ $t('products.media_files') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-2">{{ $t('products.product_image') }}</label>
                <div v-if="imagePreview" class="mb-4">
                    <img :src="imagePreview" class="w-32 h-32 object-cover rounded-md border" />
                </div>
                <FilePicker accept="image/*" label="Upload product photo" @change="onImageChange">
                    <template #icon>
                        <ImageIcon class="w-8 h-8 text-slate-400 mb-2" />
                    </template>
                </FilePicker>
                <div class="mt-2 text-sm font-medium" v-if="imageName">{{ imageName }}</div>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.image">{{ form.errors.image }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">{{ $t('products.model_file') }}</label>
                <FilePicker accept=".stl,.3mf,.step,.gcode" label="Upload 3D model 1" @change="onModelChange">
                    <template #icon>
                        <BoxIcon class="w-8 h-8 text-slate-400 mb-2" />
                    </template>
                </FilePicker>
                <div class="mt-2 text-sm font-medium" v-if="modelName">{{ modelName }}</div>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.model_file">{{ form.errors.model_file }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">{{ $t('products.model_file_2') }}</label>
                <FilePicker accept=".stl,.3mf,.step,.gcode" label="Upload 3D model 2" @change="onModelChange2">
                    <template #icon>
                        <BoxIcon class="w-8 h-8 text-slate-400 mb-2" />
                    </template>
                </FilePicker>
                <div class="mt-2 text-sm font-medium" v-if="modelName2">{{ modelName2 }}</div>
                <div class="text-red-500 text-sm mt-1" v-if="form.errors.model_file_2">{{ form.errors.model_file_2 }}</div>
            </div>
        </div>
      </Card>

      <div class="flex justify-end space-x-3">
        <Link :href="route('products.index')">
            <Button type="button" variant="outline">{{ $t('common.cancel') }}</Button>
        </Link>
        <Button class="bg-orange-500 hover:bg-orange-600 text-white" type="submit" :disabled="form.processing">
             {{ form.processing ? $t('common.loading') : $t('common.create') }}
        </Button>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import FilePicker from '@/Components/FilePicker.vue'
import { Card } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { ImageIcon, BoxIcon } from 'lucide-vue-next'

const props = defineProps({ filaments: Array })

const form = useForm({
    name: '',
    description: '',
    weight_grams: 0,
    print_time_minutes: 0,
    price: 0,
    image: null,
    model_file: null,
    model_file_2: null,
    is_active: true
})

const imageName = ref('')
const modelName = ref('')
const modelName2 = ref('')
const imagePreview = ref(null)

const onImageChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.image = file
        imageName.value = file.name
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value)
        }
        imagePreview.value = URL.createObjectURL(file)
    }
}

const onModelChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.model_file = file
        modelName.value = file.name
    }
}

const onModelChange2 = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.model_file_2 = file
        modelName2.value = file.name
    }
}

const submit = () => {
    form.post(route('products.store'))
}
</script>
