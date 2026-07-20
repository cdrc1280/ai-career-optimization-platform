<template>
  <aside class="w-56 p-4 bg-transparent rounded">
    <ul class="space-y-2 text-sm text-gray-300">
      <li><a href="/dashboard" class="block px-2 py-1 rounded hover:bg-white/5">Overview</a></li>
      <li><a href="/resumes" class="block px-2 py-1 rounded hover:bg-white/5">Resumes</a></li>
      <li><a href="/job-postings" class="block px-2 py-1 rounded hover:bg-white/5">Job Postings</a></li>
      <li><a href="/settings" class="block px-2 py-1 rounded hover:bg-white/5">Settings</a></li>
    </ul>
  </aside>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({ name: 'SidePanel' })
</script>

<style scoped>
</style>
