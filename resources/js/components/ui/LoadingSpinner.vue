<script setup lang="ts">
withDefaults(defineProps<{ size?: number }>(), { size: 20 })
</script>
<template>
  <div :style="`width:${size}px;height:${size}px`" class="spinner" />
</template>
