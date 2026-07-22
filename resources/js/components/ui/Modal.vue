<script setup lang="ts">
const emit  = defineEmits<{ close: [] }>()
const props = withDefaults(defineProps<{ title?: string; maxWidth?: string; show?: boolean }>(), { maxWidth: '560px', show: true })
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
      <div class="modal-box" :style="`max-width: ${maxWidth}`">
        <div v-if="title || $slots.header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <h3 style="font-size:1.0625rem;font-weight:600;color:var(--text-primary)">
            <slot name="header">{{ title }}</slot>
          </h3>
          <button
            class="btn btn-ghost btn-icon"
            style="font-size:1rem;color:var(--text-muted)"
            aria-label="Close"
            @click="emit('close')"
          >✕</button>
        </div>
        <div>
          <slot name="body">
            <slot />
          </slot>
        </div>
        <div v-if="$slots.footer" style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:24px">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>
