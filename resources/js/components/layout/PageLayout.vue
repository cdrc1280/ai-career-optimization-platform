<script setup lang="ts">
defineProps<{
  title: string
  subtitle?: string
}>()
</script>

<template>
  <div class="fade-in" style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Page header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
      <div>
        <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); line-height: 1.2; margin: 0;">{{ title }}</h1>
        <p v-if="subtitle" class="page-subtitle" style="font-size: 0.875rem; color: var(--text-muted); margin-top: 6px; margin-bottom: 0;">{{ subtitle }}</p>
      </div>
      
      <div v-if="$slots.actions" style="display: flex; gap: 8px; align-items: center;">
        <slot name="actions" />
      </div>
    </div>

    <!-- Main Content -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <slot />
    </div>
  </div>
</template>
