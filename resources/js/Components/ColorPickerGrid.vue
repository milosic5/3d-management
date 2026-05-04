<template>
  <div>
    <!-- Trigger -->
    <div @click="openModal" class="flex items-center gap-3">
        <div class="w-9 h-9 border border-border cursor-pointer shadow-sm rounded-sm transition hover:ring-2 hover:ring-offset-2 hover:ring-slate-300" :style="{ backgroundColor: modelValue }"></div>
        <span class="font-mono text-sm text-muted-foreground">{{ modelValue }}</span>
    </div>

    <!-- Modal -->
    <Modal :show="isOpen" @close="closeModal" maxWidth="sm">
      <div class="p-6 bg-white dark:bg-slate-800 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">{{ title }}</h3>
        
        <div class="grid grid-cols-5 gap-y-4 gap-x-2 mb-4 justify-items-center">
          <button
            v-for="color in colors"
            :key="color"
            type="button"
            class="w-[42px] h-[42px] rounded-full cursor-pointer transition-transform hover:scale-110 focus:outline-none flex items-center justify-center shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_0_0_1px_rgba(255,255,255,0.1)]"
            :style="{ backgroundColor: color }"
            @click="tempColor = color"
          >
            <svg v-if="tempColor === color" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white drop-shadow-md" :class="{'text-gray-800': isLight(color)}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-700 pt-3 pb-2 mb-4 mt-2">
          <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Custom Color</span>
          <div class="flex items-center gap-2">
              <span class="font-mono text-xs text-slate-500 uppercase">{{ tempColor }}</span>
              <input type="color" v-model="tempColor" class="w-8 h-8 rounded cursor-pointer border-0 p-0 shadow-sm" />
          </div>
        </div>        <div class="flex justify-end space-x-2">
          <button @click="closeModal" type="button" class="px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/50 rounded transition-colors uppercase tracking-wider">
            Cancel
          </button>
          <button @click="submit" type="button" class="px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/50 rounded transition-colors uppercase tracking-wider">
            Submit
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '#ffffff'
  },
  title: {
    type: String,
    default: 'Color'
  }
});

const emit = defineEmits(['update:modelValue']);

// Material colors palette + basic
const colors = [
  '#ffffff', '#000000', '#9e9e9e', '#607d8b', '#795548',
  '#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5',
  '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50',
  '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800',
  '#ff5722'
];

const isOpen = ref(false);
const tempColor = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    tempColor.value = newVal;
});

const openModal = () => {
  tempColor.value = props.modelValue || colors[0];
  isOpen.value = true;
};

const closeModal = () => {
  isOpen.value = false;
};

const submit = () => {
  emit('update:modelValue', tempColor.value);
  closeModal();
};

const isLight = (hex) => {
    // Simple light color check for checkmark contrast
    const c = hex.substring(1);
    const rgb = parseInt(c, 16);
    const r = (rgb >> 16) & 0xff;
    const g = (rgb >>  8) & 0xff;
    const b = (rgb >>  0) & 0xff;
    const luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
    return luma > 180;
};
</script>
