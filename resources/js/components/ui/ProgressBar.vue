<script setup lang="ts">
import { computed } from 'vue'
const props = withDefaults(defineProps<{ value: number; max?: number; color?: string }>(), { max: 100, color: 'primary' })
const pct = computed(() => Math.min(100, Math.max(0, (props.value / props.max) * 100)))

const gradients: Record<string, string> = {
    primary: 'linear-gradient(90deg,#3b82f6,#6366f1)',
    success: 'linear-gradient(90deg,#10b981,#34d399)',
    warning: 'linear-gradient(90deg,#f59e0b,#fbbf24)',
    danger:  'linear-gradient(90deg,#ef4444,#f87171)',
}
</script>

<template>
  <div class="progress-track">
    <div
      class="progress-fill"
      :style="{ width: pct + '%', background: gradients[color ?? 'primary'] ?? gradients.primary }"
    />
  </div>
</template>
