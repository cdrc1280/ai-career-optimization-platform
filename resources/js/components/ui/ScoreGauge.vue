<script setup lang="ts">
import { computed } from 'vue'
const props = withDefaults(defineProps<{ score: number; size?: number; label?: string }>(), { size: 120 })

const radius = 42
const circumference = 2 * Math.PI * radius
const dashOffset = computed(() => circumference - (props.score / 100) * circumference)
const color = computed(() => {
    if (props.score >= 80) return '#10b981'
    if (props.score >= 60) return '#f59e0b'
    if (props.score >= 40) return '#f97316'
    return '#ef4444'
})
</script>

<template>
  <div class="flex flex-col items-center gap-2">
    <svg :width="size" :height="size" viewBox="0 0 100 100" class="score-ring -rotate-90">
      <circle cx="50" cy="50" :r="radius" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8" />
      <circle
        cx="50" cy="50" :r="radius" fill="none"
        :stroke="color" stroke-width="8"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="dashOffset"
        stroke-linecap="round"
        style="transition: stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1)"
      />
    </svg>
    <div class="text-center -mt-2" style="margin-top: calc(-50% - 8px); position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    </div>
    <div class="relative flex flex-col items-center justify-center" :style="`margin-top: -${size * 0.65}px; height: ${size * 0.5}px`">
      <span class="font-bold text-white" :style="`font-size: ${size * 0.22}px`">{{ score }}</span>
      <span class="text-[10px] text-gray-400 uppercase tracking-wide">{{ label ?? '/100' }}</span>
    </div>
  </div>
</template>
