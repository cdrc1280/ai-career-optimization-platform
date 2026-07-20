<template>
  <div class="vue-root">
    <Topbar />
    <div class="flex">
      <SidePanel />
      <main class="flex-1 p-6">
        <Dashboard />
      </main>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import Topbar from './components/Topbar.vue'
import SidePanel from './components/SidePanel.vue'
import Dashboard from './components/Dashboard.vue'

export default defineComponent({
  name: 'App',
  components: { Topbar, SidePanel, Dashboard }
})
</script>

<style scoped>
.flex { display:flex }
.flex-1 { flex:1 }
</style>
