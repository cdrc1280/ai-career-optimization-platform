<template>
  <PageLayout title="AI Portfolio & GitHub Analyzer" subtitle="Evaluate project quality, README completeness, and repository architecture">
    <template #actions>
      <button class="btn btn-primary" @click="showAuditModal = true">Audit Portfolio</button>
    </template>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="!analyses.length" class="text-center py-16 card mt-6">
      <div class="text-6xl mb-4">💻</div>
      <h3 class="text-xl font-bold text-white mb-2">No Portfolio Audited Yet</h3>
      <p class="text-slate-400 mb-6 max-w-md mx-auto">Connect your GitHub username or portfolio URL to receive an AI analysis of project quality, README clarity, and repository structure.</p>
      <button class="btn btn-primary" @click="showAuditModal = true">Run Portfolio Audit</button>
    </div>

    <div v-else class="space-y-6 mt-6">
      <div v-for="item in analyses" :key="item.id" class="card relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-start gap-6">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <span class="text-2xl">💻</span>
              <div>
                <h3 class="text-lg font-bold text-white">{{ item.github_username ? '@' + item.github_username : 'Portfolio Site' }}</h3>
                <a v-if="item.portfolio_url" :href="item.portfolio_url" target="_blank" class="text-xs text-blue-400 hover:underline">{{ item.portfolio_url }}</a>
              </div>
            </div>

            <!-- Scores Breakdown -->
            <div class="grid grid-cols-3 gap-4 my-6">
              <div class="glass p-3 rounded-xl border border-white/05 text-center">
                <div class="text-2xl font-bold text-white">{{ item.score_data.overall_score }}<span class="text-xs text-slate-500">/100</span></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">Overall</div>
              </div>
              <div class="glass p-3 rounded-xl border border-white/05 text-center">
                <div class="text-2xl font-bold text-white">{{ item.score_data.project_quality }}<span class="text-xs text-slate-500">/100</span></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">Project Quality</div>
              </div>
              <div class="glass p-3 rounded-xl border border-white/05 text-center">
                <div class="text-2xl font-bold text-white">{{ item.score_data.readme_quality }}<span class="text-xs text-slate-500">/100</span></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">README & Docs</div>
              </div>
            </div>
          </div>

          <div class="w-full md:w-96 space-y-4">
            <div v-if="item.score_data.strengths?.length" class="glass p-4 rounded-xl border border-green-500/30 bg-green-500/5">
              <h4 class="text-xs font-semibold text-green-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Strengths
              </h4>
              <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                <li v-for="s in item.score_data.strengths" :key="s">{{ s }}</li>
              </ul>
            </div>

            <div v-if="item.score_data.suggestions?.length" class="glass p-4 rounded-xl border border-blue-500/30 bg-blue-500/5">
              <h4 class="text-xs font-semibold text-blue-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Improvement Suggestions
              </h4>
              <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                <li v-for="sug in item.score_data.suggestions" :key="sug">{{ sug }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Modal -->
    <Modal v-if="showAuditModal" :show="showAuditModal" @close="showAuditModal = false" title="Audit Portfolio or GitHub">
      <form @submit.prevent="runAudit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">GitHub Username</label>
          <input v-model="form.github_username" type="text" class="input" placeholder="e.g. octocat">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Portfolio Website URL</label>
          <input v-model="form.portfolio_url" type="url" class="input" placeholder="https://johndoe.dev">
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" class="btn btn-secondary" @click="showAuditModal = false">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="auditing">
            <span v-if="auditing" class="animate-spin mr-2">⏳</span>
            {{ auditing ? 'Analyzing Code...' : 'Audit Portfolio' }}
          </button>
        </div>
      </form>
    </Modal>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import Modal from '../components/ui/Modal.vue'
import api from '../services/api'

const analyses = ref<any[]>([])
const loading = ref(true)
const showAuditModal = ref(false)
const auditing = ref(false)
const form = ref({ github_username: '', portfolio_url: '' })

onMounted(async () => {
  await fetchAnalyses()
})

async function fetchAnalyses() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/portfolio-analyses')
    analyses.value = res.data
  } catch (e) {
    console.error('Failed to fetch portfolio analyses', e)
  } finally {
    loading.value = false
  }
}

async function runAudit() {
  if (!form.value.github_username && !form.value.portfolio_url) {
    alert('Please enter a GitHub username or portfolio URL')
    return
  }
  auditing.value = true
  try {
    const res = await api.post('/api/v1/portfolio-analyses', form.value)
    analyses.value.unshift(res.data)
    showAuditModal.value = false
    form.value = { github_username: '', portfolio_url: '' }
  } catch (e) {
    alert('Failed to audit portfolio')
  } finally {
    auditing.value = false
  }
}
</script>
