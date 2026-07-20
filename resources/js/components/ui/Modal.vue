<script setup lang="ts">
const emit  = defineEmits<{ close: [] }>()
const props = withDefaults(defineProps<{ title?: string; maxWidth?: string }>(), { maxWidth: '560px' })
</script>

<template>
  <Teleport to="body">
    <div class="modal-backdrop" @click.self="emit('close')">
      <div class="modal-box" :style="`max-width: ${maxWidth}`">
        <div v-if="title" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <h3 style="font-size:1.0625rem;font-weight:600;color:#e2e8f0">{{ title }}</h3>
          <button
            class="btn btn-ghost btn-icon"
            style="font-size:1rem;color:#94a3b8"
            aria-label="Close"
            @click="emit('close')"
          >✕</button>
        </div>
        <slot />
      </div>
    </div>
  </Teleport>
</template>
