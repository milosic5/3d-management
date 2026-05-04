<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      enter-to-class="opacity-100 translate-y-0 sm:scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 sm:scale-100"
      leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
      <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" style="background-color: rgba(17, 24, 39, 0.5);" @click="updateOpen(false)"></div>

        <!-- Modal Panel -->
        <div class="relative w-full max-w-md rounded-xl shadow-2xl overflow-hidden border border-gray-100 ring-1 ring-black/5 transform transition-all" style="background-color: #ffffff;">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-2" style="color: #111827;">
              {{ title }}
            </h3>
            <p class="text-sm leading-relaxed" style="color: #4b5563;">
              {{ description }}
            </p>
          </div>
          
          <div class="px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100" style="background-color: #f9fafb;">
            <button 
              @click="updateOpen(false)"
              class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors shadow-sm"
              style="background-color: #ffffff; color: #374151;"
            >
              {{ $t('common.cancel') }}
            </button>
            
            <button 
              @click="confirm"
              :disabled="processing"
              class="px-4 py-2 text-sm font-medium border border-transparent rounded-lg hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50"
              style="background-color: #dc2626; color: #ffffff;"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ processing ? '...' : $t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  isOpen: { type: Boolean, required: true },
  title: { type: String, required: true },
  description: { type: String, required: true },
  processing: { type: Boolean, default: false }
})

const emit = defineEmits(['update:isOpen', 'confirm'])

const updateOpen = (val) => {
  emit('update:isOpen', val)
}

const confirm = () => {
  emit('confirm')
}
</script>
