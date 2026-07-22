<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useJobPostingsStore } from '../stores/jobPostings'
import { useResumesStore } from '../stores/resumes'
import { useAnalysisStore } from '../stores/analysis'
import { useRouter } from 'vue-router'
import Modal from '../components/ui/Modal.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import { addToast } from '../composables/toast'
import ConfirmModal from '../components/ConfirmModal.vue'
import PageLayout from '../components/layout/PageLayout.vue'

const jobsStore = useJobPostingsStore()
const resumesStore = useResumesStore()
const analysisStore = useAnalysisStore()
const router = useRouter()

const addTab = ref<'url' | 'text'>('url')
const jobUrl = ref('')
const jobText = ref('')
const showAnalyzeModal = ref(false)
const showAddModal = ref(false)
const analyzeTarget = ref<any>(null)
const selectedVersionId = ref<number | null>(null)
const deleteModal = ref({ show: false, id: 0 })

onMounted(async () => {
    await Promise.all([jobsStore.fetch(), resumesStore.fetch()])
})

async function submitUrl() {
    if (!jobUrl.value.trim()) return
    try {
        await jobsStore.create({ source_url: jobUrl.value, source_type: 'url' })
        jobUrl.value = ''
        showAddModal.value = false
        addToast('success', 'Job posting added! Extracting details...')
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Failed to add job.')
    }
}

async function submitText() {
    if (!jobText.value.trim()) return
    try {
        await jobsStore.create({ raw_description: jobText.value, source_type: 'manual' })
        jobText.value = ''
        showAddModal.value = false
        addToast('success', 'Job posting added! Extracting details...')
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Failed to add job.')
    }
}

function openAnalyze(job: any) {
    analyzeTarget.value = job
    showAnalyzeModal.value = true
    selectedVersionId.value = null
}

const allVersions = computed(() => {
    const vers: any[] = []
    resumesStore.resumes.forEach(r => {
        if (r.master_version) {
            vers.push({ ...r.master_version, resume_name: r.original_filename })
        }
    })
    return vers
})

async function runAnalysis() {
    if (!selectedVersionId.value || !analyzeTarget.value) return
    showAnalyzeModal.value = false
    addToast('info', 'Analysis started...')
    try {
        await analysisStore.analyze(selectedVersionId.value, analyzeTarget.value.id)
        addToast('success', 'Analysis complete! View results in Analysis tab.')
        router.push('/analysis')
    } catch (e: any) {
        addToast('error', 'Analysis failed. Please try again.')
    }
}

function confirmDelete(id: number) {
    deleteModal.value = { show: true, id }
}

async function deleteJob() {
    await jobsStore.remove(deleteModal.value.id)
    deleteModal.value.show = false
    addToast('success', 'Job posting deleted.')
}
</script>

<template>
  <PageLayout title="Job Postings" subtitle="Track the jobs you want to apply for and optimize your resume against them">
    <template #actions>
      <button class="btn btn-primary" @click="showAddModal = true">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Job
      </button>
    </template>

    <!-- Job list -->
    <div class="card">
      <h3 class="font-semibold text-white mb-4">Saved Jobs ({{ jobsStore.jobPostings.length }})</h3>
      <div v-if="jobsStore.loading && jobsStore.jobPostings.length === 0" class="flex justify-center py-8"><LoadingSpinner :size="28" /></div>
      <div v-else-if="jobsStore.jobPostings.length === 0" class="text-center py-12 text-slate-500">
        <div class="text-4xl mb-3">🔍</div>
        <p>No job postings added yet. Click 'Add Job' to get started.</p>
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="job in jobsStore.jobPostings" :key="job.id"
          class="p-4 rounded-xl glass border border-white/06 hover:border-blue-500/20 transition-all"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h4 class="font-semibold text-white">{{ job.job_title || 'Untitled Job' }}</h4>
                <StatusBadge :status="job.extraction_status ?? 'pending'" />
              </div>
              <p class="text-slate-400 text-sm mt-0.5">{{ job.company_name || 'Unknown Company' }}</p>
              <div v-if="job.required_skills?.length" class="flex flex-wrap gap-1.5 mt-2">
                <span v-for="skill in (job.required_skills ?? []).slice(0, 6)" :key="skill"
                  class="badge badge-blue text-[10px]">{{ skill }}</span>
                <span v-if="job.required_skills.length > 6" class="text-slate-500 text-xs">+{{ job.required_skills.length - 6 }}</span>
              </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
              <button class="btn btn-primary btn-sm" @click="openAnalyze(job)">🎯 Analyze</button>
              <button class="btn btn-danger btn-sm" @click="confirmDelete(job.id)">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <Modal v-if="showAddModal" title="Add Job Posting" @close="showAddModal = false">
      <div class="tab-bar mb-4">
        <button :class="['tab-item', addTab === 'url' && 'active']" @click="addTab = 'url'">Paste URL</button>
        <button :class="['tab-item', addTab === 'text' && 'active']" @click="addTab = 'text'">Paste Description</button>
      </div>

      <div v-if="addTab === 'url'" class="flex gap-2">
        <input v-model="jobUrl" type="url" class="input flex-1" placeholder="https://careers.company.com/job/12345" @keyup.enter="submitUrl" />
        <button class="btn btn-primary flex-shrink-0" :disabled="jobsStore.creating" @click="submitUrl">
          <LoadingSpinner v-if="jobsStore.creating" :size="14" />
          Extract
        </button>
      </div>
      <div v-else class="space-y-2">
        <textarea v-model="jobText" class="input" rows="8" placeholder="Paste the full job description here..." />
        <div class="flex justify-end">
          <button class="btn btn-primary" :disabled="jobsStore.creating" @click="submitText">
            <LoadingSpinner v-if="jobsStore.creating" :size="14" />
            Extract Details
          </button>
        </div>
      </div>
    </Modal>

    <!-- Analyze modal -->
    <Modal v-if="showAnalyzeModal" title="Analyze Resume" @close="showAnalyzeModal = false">
      <p class="text-slate-400 text-sm mb-4">
        Analyzing <strong class="text-white">{{ analyzeTarget?.job_title }}</strong> at <strong class="text-white">{{ analyzeTarget?.company_name }}</strong>
      </p>
      <p class="text-slate-400 text-sm mb-3">Select your resume version to analyze:</p>
      <div class="space-y-2 max-h-60 overflow-y-auto mb-4">
        <div v-if="allVersions.length === 0" class="text-center py-6 text-slate-500 text-sm">
          No resumes with completed parsing found.
        </div>
        <div
          v-for="ver in allVersions" :key="ver.id"
          :class="['p-3 rounded-lg cursor-pointer border transition-all', selectedVersionId === ver.id ? 'border-blue-500 bg-blue-500/10' : 'border-white/06 glass-hover']"
          @click="selectedVersionId = ver.id"
        >
          <p class="font-medium text-white text-sm">{{ ver.resume_name }}</p>
          <p class="text-slate-400 text-xs">{{ ver.label }}</p>
        </div>
      </div>
      <div class="flex gap-2 justify-end">
        <button class="btn btn-secondary" @click="showAnalyzeModal = false">Cancel</button>
        <button class="btn btn-primary" :disabled="!selectedVersionId || analysisStore.analyzing" @click="runAnalysis">
          <LoadingSpinner v-if="analysisStore.analyzing" :size="14" />
          {{ analysisStore.analyzing ? 'Analyzing...' : 'Run Analysis' }}
        </button>
      </div>
    </Modal>

    <ConfirmModal 
      :show="deleteModal.show" 
      title="Delete Job Posting"
      message="Are you sure you want to delete this job posting? This action cannot be undone."
      @close="deleteModal.show = false"
      @confirm="deleteJob"
    />
  </PageLayout>
</template>
