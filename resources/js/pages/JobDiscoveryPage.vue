<template>
  <PageLayout title="AI Job Discovery" subtitle="Smart job recommendations matched against your profile, experience, and skills">
    <!-- Filters Bar -->
    <div class="card mb-6">
      <form @submit.prevent="searchJobs" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <input v-model="filters.keyword" type="text" class="input w-full" placeholder="Job title, skill, or keyword (e.g. Senior Laravel Developer)">
        </div>
        <div class="w-full md:w-64">
          <input v-model="filters.location" type="text" class="input w-full" placeholder="Location or Remote (e.g. Manila / Remote)">
        </div>
        <button type="submit" class="btn btn-primary flex items-center justify-center gap-2" :disabled="searching">
          <span v-if="searching" class="animate-spin">⏳</span>
          <span>Search Jobs</span>
        </button>
      </form>
    </div>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="!jobs.length" class="text-center py-16 card">
      <div class="text-6xl mb-4">🔍</div>
      <h3 class="text-xl font-bold text-white mb-2">No Matching Jobs Found</h3>
      <p class="text-slate-400 mb-6 max-w-md mx-auto">Try searching for different keywords or locations to discover AI-scored opportunities.</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="job in jobs" :key="job.id" class="card hover:border-blue-500/30 transition-colors relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="text-xl font-bold text-white">{{ job.title }}</h3>
              <span class="badge badge-slate text-[10px]">{{ job.source }}</span>
            </div>
            
            <p class="text-blue-400 font-medium text-sm mb-3">{{ job.company }} • {{ job.location }} • {{ job.salary }}</p>

            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300">
              <div v-if="job.required_skills?.length" class="flex items-center gap-1.5">
                <span class="text-slate-400">Required:</span>
                <span v-for="s in job.required_skills" :key="s" class="badge badge-blue text-[11px]">{{ s }}</span>
              </div>
              
              <div v-if="job.missing_skills?.length" class="flex items-center gap-1.5">
                <span class="text-slate-400">Missing:</span>
                <span v-for="s in job.missing_skills" :key="s" class="badge badge-red text-[11px]">{{ s }}</span>
              </div>
            </div>
          </div>

          <!-- AI Score & Action -->
          <div class="flex flex-row md:flex-col items-center md:items-end justify-between gap-4 flex-shrink-0 border-t md:border-t-0 pt-4 md:pt-0 border-slate-800">
            <div class="text-right">
              <div class="flex items-center gap-2 justify-end">
                <span class="text-xs text-slate-400">AI Match</span>
                <span :class="['text-xl font-black', job.match_score >= 85 ? 'text-green-400' : job.match_score >= 70 ? 'text-yellow-400' : 'text-slate-400']">
                  {{ job.match_score }}%
                </span>
              </div>
              <div class="text-[11px] text-slate-500">ATS Pass: {{ job.ats_compatibility }}%</div>
            </div>

            <div class="flex items-center gap-2">
              <button @click="saveJob(job)" class="btn btn-secondary btn-sm flex items-center gap-1.5" :disabled="savingId === job.id">
                <svg class="w-4 h-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                {{ savingId === job.id ? 'Saving...' : 'Save Job' }}
              </button>
              <a v-if="job.apply_url" :href="job.apply_url" target="_blank" class="btn btn-primary btn-sm">
                Apply →
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import api from '../services/api'

const jobs = ref<any[]>([])
const loading = ref(true)
const searching = ref(false)
const savingId = ref<string | null>(null)
const filters = ref({ keyword: '', location: '' })

onMounted(async () => {
  await searchJobs()
})

async function searchJobs() {
  searching.value = true
  try {
    const res = await api.get('/api/v1/jobs/discovery', { params: filters.value })
    jobs.value = res.data
  } catch (e) {
    console.error('Failed to search jobs', e)
  } finally {
    loading.value = false
    searching.value = false
  }
}

async function saveJob(job: any) {
  savingId.value = job.id
  try {
    await api.post('/api/v1/job-postings', {
      company: job.company,
      title: job.title,
      location: job.location,
      salary_range: job.salary,
      job_description: `Required Skills: ${job.required_skills?.join(', ')}\nSource: ${job.source}`,
      url: job.apply_url
    })
    alert('Job saved to Job Tracker!')
  } catch (e) {
    alert('Failed to save job')
  } finally {
    savingId.value = null
  }
}
</script>
