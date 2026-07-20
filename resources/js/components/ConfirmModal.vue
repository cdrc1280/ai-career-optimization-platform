<template>
  <Modal :show="show" @close="$emit('close')">
    <template #header>
      {{ title }}
    </template>
    
    <template #body>
      <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
          <div :class="[
            'w-10 h-10 rounded-full flex items-center justify-center bg-opacity-20',
            type === 'danger' ? 'bg-red-500 text-red-500' : 'bg-blue-500 text-blue-500'
          ]">
            <svg v-if="type === 'danger'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-1 text-sm text-slate-300">
          <slot name="message">{{ message }}</slot>
        </div>
      </div>
    </template>
    
    <template #footer>
      <button class="btn btn-secondary" @click="$emit('close')" :disabled="loading">
        {{ cancelText }}
      </button>
      <button :class="['btn', type === 'danger' ? 'btn-danger' : 'btn-primary']" @click="$emit('confirm')" :disabled="loading">
        <span v-if="loading" class="mr-2">
          <svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>
        {{ confirmText }}
      </button>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import Modal from './Modal.vue'

defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  type: {
    type: String,
    default: 'danger' // 'danger' or 'info'
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['close', 'confirm'])
</script>
