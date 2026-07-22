<template>
  <PageLayout title="Browser Extension Hub" subtitle="Analyze job postings live on LinkedIn, Indeed, JobStreet, and Glassdoor without leaving the page">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
      <!-- Token Generator Card -->
      <div class="card space-y-4">
        <h3 class="font-bold text-white text-lg flex items-center gap-2">
          <span>🧩</span> Extension Connection Token
        </h3>
        <p class="text-slate-300 text-sm">
          Connect your Chrome or Firefox extension to your CareerAI account using this secret API token.
        </p>

        <div class="glass p-4 rounded-xl border border-white/05 bg-slate-900/50">
          <span class="text-xs text-slate-400 font-medium block mb-1">Your Extension Token</span>
          <div class="flex items-center gap-2">
            <input :type="showToken ? 'text' : 'password'" :value="token || 'cai_ext_89127391823719827391'" class="input flex-1 font-mono text-xs" readonly>
            <button @click="showToken = !showToken" class="btn btn-secondary btn-sm">{{ showToken ? 'Hide' : 'Show' }}</button>
            <button @click="copyToken" class="btn btn-primary btn-sm">Copy</button>
          </div>
        </div>

        <div class="space-y-2 pt-2">
          <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Supported Sites</h4>
          <div class="flex flex-wrap gap-2">
            <span class="badge badge-blue">LinkedIn</span>
            <span class="badge badge-blue">Indeed</span>
            <span class="badge badge-blue">JobStreet</span>
            <span class="badge badge-blue">Glassdoor</span>
            <span class="badge badge-blue">Greenhouse</span>
            <span class="badge badge-blue">Lever</span>
          </div>
        </div>
      </div>

      <!-- Extension Live Preview Simulator -->
      <div class="card space-y-4 relative overflow-hidden border-blue-500/30">
        <div class="flex items-center justify-between">
          <h3 class="font-bold text-white text-lg flex items-center gap-2">
            <span>⚡</span> Extension Widget Simulator
          </h3>
          <span class="badge badge-green text-[10px]">Extension Active</span>
        </div>

        <p class="text-slate-300 text-sm">
          Test how the floating extension overlay analyzes job posts live:
        </p>

        <form @submit.prevent="runExtensionTest" class="space-y-3">
          <textarea v-model="sampleJd" class="input h-28 text-xs" placeholder="Paste any raw Job Posting description to test live parsing..." required></textarea>
          <button type="submit" class="btn btn-primary btn-sm w-full" :disabled="testing">
            <span v-if="testing" class="animate-spin mr-2">⏳</span>
            {{ testing ? 'Analyzing via Extension Engine...' : 'Run Extension Live Match' }}
          </button>
        </form>

        <div v-if="testResult" class="glass p-4 rounded-xl border border-blue-500/30 bg-blue-500/5 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400">Calculated Match</span>
            <span class="font-bold text-green-400 text-base">{{ testResult.match_score }}%</span>
          </div>
          <p class="text-xs text-slate-300">{{ testResult.summary }}</p>
          <div class="flex items-center justify-between pt-2 border-t border-slate-700/50">
            <span class="text-[11px] text-slate-400">Estimated ATS Score</span>
            <span class="font-bold text-white text-xs">{{ testResult.ats_compatibility }}%</span>
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import api from '../services/api'

const token = ref('cai_ext_98217398127391823719')
const showToken = ref(false)
const sampleJd = ref('Looking for a Senior Backend Engineer proficient in Laravel 11, Vue 3, Redis caching, microservices, and CI/CD pipelines...')
const testing = ref(false)
const testResult = ref<any>(null)

function copyToken() {
  navigator.clipboard.writeText(token.value)
  alert('Extension token copied!')
}

async function runExtensionTest() {
  testing.value = true
  try {
    const res = await api.post('/api/v1/extension/analyze', { text: sampleJd.value })
    testResult.value = res.data
  } catch (e) {
    alert('Extension test failed')
  } finally {
    testing.value = false
  }
}
</script>
