<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeOnOutsideClick && $emit('close')"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-lg bg-[#0d1629] border border-white/10 shadow-2xl rounded-2xl overflow-hidden z-10 mx-4 transform transition-all" role="dialog">
          
          <!-- Header -->
          <div class="px-6 py-4 border-b border-white/5 flex justify-between items-center bg-[#111e36]">
            <h3 class="text-lg font-semibold text-white">
              <slot name="header">Title</slot>
            </h3>
            <button @click="$emit('close')" class="text-slate-400 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Body -->
          <div class="p-6 text-slate-300">
            <slot name="body">
              Modal content goes here.
            </slot>
          </div>
          
          <!-- Footer -->
          <div v-if="$slots.footer" class="px-6 py-4 border-t border-white/5 bg-[#080e1a] flex justify-end gap-3">
            <slot name="footer"></slot>
          </div>
          
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  closeOnOutsideClick: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close'])

const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.show) {
    emit('close')
  }
}

watch(() => props.show, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95) translateY(10px);
}
</style>
