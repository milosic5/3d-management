<template>
  <div class="relative" ref="container">
    <div 
      class="w-full h-10 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus-within:ring-1 focus-within:ring-slate-950 dark:border-slate-800 dark:bg-slate-950 dark:focus-within:ring-slate-300 flex items-center justify-between cursor-pointer"
      :class="{ 'border-red-500': error }"
      @click="toggle"
    >
      <span class="truncate" :class="{ 'text-slate-500': !selectedOption }">
        {{ selectedOption ? selectedOption.label : placeholder }}
      </span>
      <ChevronDownIcon class="h-4 w-4 opacity-50" />
    </div>

    <div v-if="isOpen" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-md shadow-md flex flex-col max-h-80">
      <div class="p-2 border-b border-slate-200 dark:border-slate-800">
        <input 
          ref="searchInput"
          type="text" 
          v-model="search" 
          class="w-full h-8 px-2 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded focus:ring-1 focus:ring-slate-950 dark:focus:ring-slate-300 focus:outline-none" 
          :placeholder="searchPlaceholder"
          @click.stop
        />
      </div>
      <div class="overflow-y-auto p-1 flex-1 min-h-0">
        <div 
          v-for="option in filteredOptions" 
          :key="option.value"
          class="px-2 py-1.5 text-sm rounded-sm cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center"
          :class="{ 'bg-slate-100 dark:bg-slate-800 font-medium': modelValue === option.value }"
          @click.stop="select(option)"
        >
          {{ option.label }}
        </div>
        <div v-if="filteredOptions.length === 0" class="py-2 text-center text-sm text-slate-500">
          {{ noResultsText }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { ChevronDownIcon } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Select an option' },
  searchPlaceholder: { type: String, default: 'Search...' },
  noResultsText: { type: String, default: 'No results found.' },
  error: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const search = ref('')
const searchInput = ref(null)
const container = ref(null)

const toggle = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    search.value = ''
    nextTick(() => {
      if (searchInput.value) searchInput.value.focus()
    })
  }
}

const select = (option) => {
  emit('update:modelValue', option.value)
  emit('change', option.value)
  isOpen.value = false
}

const filteredOptions = computed(() => {
  if (!search.value) return props.options
  const q = search.value.toLowerCase()
  return props.options.filter(o => o.label.toLowerCase().includes(q))
})

const selectedOption = computed(() => {
  return props.options.find(o => o.value === props.modelValue)
})

const handleClickOutside = (e) => {
  if (container.value && !container.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
