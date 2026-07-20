<template>
  <section>
    <header class="mb-3">
      <h1 class="text-2xl font-semibold">Dashboard</h1>
      <p class="text-sm text-gray-400">Welcome back — here's a quick overview.</p>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card3D v-for="n in 9" :key="n" :index="n" />
    </div>

    <section class="api-actions mt-6 p-4 bg-white/3 rounded">
      <h2 class="font-semibold mb-2">API Actions</h2>
      <div class="flex gap-2 flex-wrap">
        <button class="btn" @click="call('resumes')">GET Resumes</button>
        <button class="btn" @click="call('job-postings')">GET Job Postings</button>
        <button class="btn" @click="call('resume-analysis')">POST Resume Analysis</button>
        <button class="btn" @click="call('resume-optimization')">POST Resume Optimization</button>
        <button class="btn" @click="call('cover-letter')">POST Cover Letter</button>
        <button class="btn" @click="call('application')">POST Application</button>
      </div>

      <pre class="mt-4 bg-black/40 p-3 rounded text-xs overflow-auto" v-if="lastResponse">{{ lastResponse }}</pre>
    </section>
  </section>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import Card3D from './Card3D.vue'
import * as api from '../services/api'

export default defineComponent({
  name: 'Dashboard',
  components: { Card3D },
  setup() {
    const lastResponse = ref<string | null>(null)

    async function call(action: string) {
      lastResponse.value = 'Loading...'
      try {
        let res
        switch (action) {
          case 'resumes':
            res = await api.getResumes()
            break
          case 'job-postings':
            res = await api.getJobPostings()
            break
          case 'resume-analysis':
            res = await api.postResumeAnalysis({ resume_id: 1, job_posting_id: 1 })
            break
          case 'resume-optimization':
            res = await api.postResumeOptimization({ resume_id: 1, prompt: 'Optimize' })
            break
          case 'cover-letter':
            res = await api.postCoverLetter({ resume_id: 1, job_posting_id: 1 })
            break
          case 'application':
            res = await api.createApplication({ job_posting_id: 1, resume_id: 1 })
            break
          default:
            res = { data: { message: 'Unknown action' } }
        }
        lastResponse.value = JSON.stringify(res.data, null, 2)
      } catch (err: any) {
        lastResponse.value = err?.response?.data ? JSON.stringify(err.response.data, null, 2) : String(err)
      }
    }

    return { lastResponse, call }
  }
})
</script>

<style scoped>
.api-actions .btn{margin:4px}
</style>

