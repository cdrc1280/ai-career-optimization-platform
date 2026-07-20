<script setup lang="ts">
import { toastState, removeToast } from '../../composables/toast'

const icons = {
    success: '✓',
    error:   '✕',
    warning: '⚠',
    info:    'ℹ',
}
const classes = {
    success: 'toast-success',
    error:   'toast-error',
    warning: 'toast-warning',
    info:    'toast-info',
}
</script>

<template>
  <Teleport to="body">
    <div class="toast-container" role="alert" aria-live="polite">
      <TransitionGroup name="toast-slide" tag="div" class="flex flex-col gap-2">
        <div
          v-for="t in toastState.items"
          :key="t.id"
          :class="['toast', classes[t.type as keyof typeof classes]]"
        >
          <div class="flex items-start gap-3">
            <span class="text-base leading-none flex-shrink-0 mt-0.5">{{ icons[t.type as keyof typeof icons] }}</span>
            <div class="flex-1 min-w-0">
              <p v-if="t.title" class="font-semibold text-sm">{{ t.title }}</p>
              <p class="text-sm" :class="t.title ? 'opacity-80 mt-0.5' : ''">{{ t.message }}</p>
            </div>
            <button class="opacity-50 hover:opacity-100 transition-opacity text-sm leading-none flex-shrink-0" @click="removeToast(t.id)">✕</button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-slide-enter-active { animation: toast-in 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-slide-leave-active { animation: toast-out 0.25s ease forwards; }

@keyframes toast-in {
  from { opacity: 0; transform: translateX(100%); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes toast-out {
  from { opacity: 1; transform: translateX(0); max-height: 120px; }
  to   { opacity: 0; transform: translateX(100%); max-height: 0; margin: 0; padding: 0; }
}
</style>
