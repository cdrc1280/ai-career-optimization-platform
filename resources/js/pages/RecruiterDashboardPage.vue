<template>
  <PageLayout title="Enterprise Recruiter Platform" subtitle="AI Candidate Ranking, Resume Screening, and Team Collaboration Hub">
    <template #actions>
      <button class="btn btn-primary" @click="showBulkModal = true">⚡ Run Bulk Screening</button>
    </template>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else class="space-y-6 mt-6">
      <!-- Top Metrics -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card glass">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Active Pipeline</span>
          <div class="text-3xl font-black text-white">{{ candidates.length }} Candidates</div>
          <p class="text-[11px] text-slate-500 mt-2">Ranked by ATS match score</p>
        </div>
        <div class="card glass">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Avg Candidate Match</span>
          <div class="text-3xl font-black text-green-400">87%</div>
          <p class="text-[11px] text-slate-500 mt-2">High alignment with active job posts</p>
        </div>
        <div class="card glass">
          <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block mb-1">Team Collaboration</span>
          <div class="text-3xl font-black text-blue-400">Active</div>
          <p class="text-[11px] text-slate-500 mt-2">Interview notes and ratings shared</p>
        </div>
      </div>

      <!-- Candidate Ranking Table -->
      <div class="card">
        <h3 class="font-bold text-white text-lg mb-4">Candidate Ranking & AI Screening</h3>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-700/50 text-slate-400 text-xs uppercase tracking-wider">
                <th class="py-3 px-4">Rank</th>
                <th class="py-3 px-4">Candidate Name</th>
                <th class="py-3 px-4">Target Role</th>
                <th class="py-3 px-4">Experience</th>
                <th class="py-3 px-4">ATS Match</th>
                <th class="py-3 px-4">Matching Skills</th>
                <th class="py-3 px-4">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-sm">
              <tr v-for="(c, idx) in candidates" :key="c.id || idx" class="hover:bg-slate-800/30 transition-colors">
                <td class="py-3 px-4 font-black text-blue-400">#{{ idx + 1 }}</td>
                <td class="py-3 px-4 font-bold text-white">{{ c.name }}</td>
                <td class="py-3 px-4 text-slate-300">{{ c.role }}</td>
                <td class="py-3 px-4 text-slate-400 text-xs">{{ c.experience }}</td>
                <td class="py-3 px-4">
                  <span :class="['font-bold text-sm', c.match_score >= 85 ? 'text-green-400' : 'text-yellow-400']">
                    {{ c.match_score }}%
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="skill in c.skills" :key="skill" class="badge badge-blue text-[10px]">{{ skill }}</span>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <button @click="openCandidate(c)" class="btn btn-secondary btn-sm">Review Notes</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Bulk Screening Modal -->
    <Modal v-if="showBulkModal" :show="showBulkModal" @close="showBulkModal = false" title="Run AI Bulk Candidate Screening">
      <form @submit.prevent="runBulkScreening" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Paste Candidate Resumes / Text</label>
          <textarea v-model="bulkText" class="input h-36" placeholder="Paste candidate profiles, one per line or separated by paragraphs..." required></textarea>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" class="btn btn-secondary" @click="showBulkModal = false">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="screening">
            <span v-if="screening" class="animate-spin mr-2">⏳</span>
            {{ screening ? 'Screening Candidates...' : 'Screen & Rank Candidates' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Candidate Detail Modal -->
    <Modal v-if="selectedCandidate" :show="!!selectedCandidate" @close="selectedCandidate = null" :title="'Candidate: ' + selectedCandidate.name">
      <div class="space-y-4">
        <div class="flex items-center justify-between glass p-3 rounded-lg border border-white/05">
          <span class="text-sm text-slate-300">Match Score</span>
          <span class="font-bold text-green-400 text-lg">{{ selectedCandidate.match_score }}%</span>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Hiring Team Notes</label>
          <textarea v-model="noteInput" class="input h-24" placeholder="Add feedback, interview ratings, or red flags..."></textarea>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" class="btn btn-secondary" @click="selectedCandidate = null">Close</button>
          <button type="button" class="btn btn-primary" @click="saveNote">Save Notes</button>
        </div>
      </div>
    </Modal>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import Modal from '../components/ui/Modal.vue'
import api from '../services/api'

const candidates = ref<any[]>([])
const loading = ref(true)
const showBulkModal = ref(false)
const screening = ref(false)
const bulkText = ref('')
const selectedCandidate = ref<any>(null)
const noteInput = ref('')

onMounted(async () => {
  await fetchCandidates()
})

async function fetchCandidates() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/recruiter/candidates')
    candidates.value = res.data
  } catch (e) {
    console.error('Failed to fetch candidates', e)
  } finally {
    loading.value = false
  }
}

async function runBulkScreening() {
  screening.value = true
  try {
    const res = await api.post('/api/v1/recruiter/bulk-screen', { text: bulkText.value })
    candidates.value = res.data
    showBulkModal.value = false
    bulkText.value = ''
  } catch (e) {
    alert('Failed to screen candidates')
  } finally {
    screening.value = false
  }
}

function openCandidate(c: any) {
  selectedCandidate.value = c
  noteInput.value = c.notes || ''
}

async function saveNote() {
  if (!selectedCandidate.value) return
  try {
    await api.post('/api/v1/recruiter/notes', { id: selectedCandidate.value.id, notes: noteInput.value })
    selectedCandidate.value.notes = noteInput.value
    selectedCandidate.value = null
    alert('Note saved successfully!')
  } catch (e) {
    alert('Failed to save note')
  }
}
</script>
