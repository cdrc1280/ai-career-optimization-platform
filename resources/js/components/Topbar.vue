<template>
    <header class="w-full bg-transparent py-3">
        <div class="container mx-auto flex items-center gap-4">
            <div class="text-accent font-bold text-lg">{{ appName }}</div>
            <nav class="ml-auto flex gap-4 text-sm text-gray-300">
                <a href="/">Home</a>
                <a href="/dashboard">Dashboard</a>
                <a href="/job-postings">Jobs</a>
                <a href="/resumes">Resumes</a>
            </nav>
        </div>
    </header>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'Topbar',
    data() {
        return { appName: (document.title || 'AI Career') }
    }
})
</script>

<style scoped>
.text-accent {
    color: #60a5fa
}
</style>
