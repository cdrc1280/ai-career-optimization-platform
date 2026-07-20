<script setup lang="ts">
import { RouterView } from 'vue-router'
import ToastSystem from './components/ui/ToastSystem.vue'
</script>

<template>
  <!-- Auth routes (login/register) render directly; app routes via AppShell -->
  <RouterView />
  <ToastSystem />
</template>
