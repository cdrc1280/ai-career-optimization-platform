<template>
  <PageLayout title="Career Analytics & A/B Testing" subtitle="Track your resume version performance, interview conversion rate, and offer rate">
    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else class="space-y-6 mt-6">
      <!-- Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card glass relative overflow-hidden">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Total Applications</span>
          <div class="text-3xl font-black text-white">{{ stats.total_applications || 0 }}</div>
          <p class="text-[11px] text-slate-500 mt-2">Tracked across all jobs</p>
        </div>
        <div class="card glass relative overflow-hidden">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Interview Rate</span>
          <div class="text-3xl font-black text-green-400">{{ stats.interview_rate || 0 }}%</div>
          <p class="text-[11px] text-slate-500 mt-2">Applications invited to interview</p>
        </div>
        <div class="card glass relative overflow-hidden">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Offer Rate</span>
          <div class="text-3xl font-black text-blue-400">{{ stats.offer_rate || 0 }}%</div>
          <p class="text-[11px] text-slate-500 mt-2">Interview to offer conversion</p>
        </div>
        <div class="card glass relative overflow-hidden">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Rejection Rate</span>
          <div class="text-3xl font-black text-slate-400">{{ stats.rejection_rate || 0 }}%</div>
          <p class="text-[11px] text-slate-500 mt-2">Archived / Rejected</p>
        </div>
      </div>

      <!-- Resume Version A/B Performance Table -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="font-bold text-white text-lg">Resume A/B Testing Performance</h3>
            <p class="text-xs text-slate-400">Compare how different resume versions perform in real job applications</p>
          </div>
          <span class="badge badge-blue">A/B Testing Active</span>
        </div>

        <div v-if="!stats.resume_versions?.length" class="text-center py-8 text-slate-500 text-sm">
          No resume version data available yet. Create different versions in the Resumes page and link them to applications!
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-700/50 text-slate-400 text-xs uppercase tracking-wider">
                <th class="py-3 px-4">Resume Version</th>
                <th class="py-3 px-4">Applications</th>
                <th class="py-3 px-4">Interviews</th>
                <th class="py-3 px-4">Offers</th>
                <th class="py-3 px-4">Avg Match Score</th>
                <th class="py-3 px-4">Performance Rating</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-sm">
              <tr v-for="ver in stats.resume_versions" :key="ver.id" class="hover:bg-slate-800/30 transition-colors">
                <td class="py-3 px-4 font-semibold text-white">
                  {{ ver.version_name }}
                  <span v-if="ver.is_master" class="badge badge-green ml-2 text-[10px]">Master</span>
                </td>
                <td class="py-3 px-4 text-slate-300">{{ ver.applications_count }}</td>
                <td class="py-3 px-4 text-green-400 font-bold">{{ ver.interview_count }}</td>
                <td class="py-3 px-4 text-blue-400 font-bold">{{ ver.offer_count }}</td>
                <td class="py-3 px-4 text-slate-300">{{ ver.match_score_avg }}%</td>
                <td class="py-3 px-4">
                  <span :class="['badge', ver.interview_count > 2 ? 'badge-green' : ver.applications_count > 0 ? 'badge-blue' : 'badge-slate']">
                    {{ ver.interview_count > 2 ? '🔥 Top Performer' : ver.applications_count > 0 ? 'Active' : 'Untested' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import api from '../services/api'

const stats = ref<any>({})
const loading = ref(true)

onMounted(async () => {
  await fetchStats()
})

async function fetchStats() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/analytics/performance')
    stats.value = res.data
  } catch (e) {
    console.error('Failed to fetch analytics stats', e)
  } finally {
    loading.value = false
  }
}
</script>
