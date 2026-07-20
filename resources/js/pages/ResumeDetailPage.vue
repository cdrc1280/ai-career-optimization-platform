<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useResumesStore } from '../stores/resumes'
import { useJobPostingsStore } from '../stores/jobPostings'
import { compareResumeVersion, triggerOptimization, exportResumeVersion , deleteResumeVersion, setMasterVersion } from '../services/api'
import Modal from '../components/ui/Modal.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import { addToast } from '../composables/toast'

const isDeleting = ref(false)

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this version?')) return
    isDeleting.value = true
    try {
        await deleteResumeVersion(route.params.id as any)
        addToast('success', 'Version deleted')
        router.push('/resumes')
    } catch {
        addToast('error', 'Failed to delete')
    } finally {
        isDeleting.value = false
    }
}

async function handleSetMaster() {
    if (!confirm('Set this as the master version?')) return
    try {
        await setMasterVersion(route.params.id as any)
        addToast('success', 'Set as master version')
        // fetchResume() - assuming it's fetch() or something similar
    } catch {
        addToast('error', 'Failed to set master')
    }
}
const router = useRouter()
const route = useRoute()
const resumesStore = useResumesStore()
const jobsStore = useJobPostingsStore()

const showOptimizeModal = ref(false)
const selectedJobId = ref<number | null>(null)
const optimizing = ref(false)

const showDiff = ref(false)
const diffData = ref<any>(null)
const diffLoading = ref(false)
const selectedVersionId = ref<number | null>(null)

onMounted(async () => {
    await resumesStore.fetchOne(Number(route.params.id))
    await jobsStore.fetch()
})

const resume = computed(() => resumesStore.current)
const versions = computed(() => resume.value?.versions ?? [])

async function loadDiff(versionId: number) {
    selectedVersionId.value = versionId
    diffLoading.value = true
    showDiff.value = true
    try {
        const res = await compareResumeVersion(versionId)
        diffData.value = res.data
    } catch {
        diffData.value = null
    } finally { diffLoading.value = false }
}

async function optimize() {
    if (!selectedJobId.value) return
    optimizing.value = true
    try {
        const masterVersion = versions.value.find((v: any) => v.is_master)
        if (!masterVersion) throw new Error('No master version found.')
        await triggerOptimization({ resume_version_id: masterVersion.id, job_posting_id: selectedJobId.value })
        addToast('success', 'Optimization started! Polling for results...')
        showOptimizeModal.value = false
        // Poll for new version
        let attempts = 0
        while (attempts < 20) {
            await new Promise(r => setTimeout(r, 3000))
            await resumesStore.fetchOne(Number(route.params.id))
            attempts++
            const newVersion = versions.value.find((v: any) => !v.is_master && v.job_posting_id === selectedJobId.value)
            if (newVersion) {
                addToast('success', 'Optimization complete!')
                return
            }
        }
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Optimization failed.')
    } finally { optimizing.value = false }
}

function downloadUrl(versionId: number, format: string) {
    return exportResumeVersion(versionId, format)
}
</script>

<template>
  <div class="fade-in space-y-6">
    <button class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition-colors" @click="router.back()">
      ← Back to Resumes
    </button>

    <div v-if="!resume" class="flex justify-center py-20"><LoadingSpinner :size="36" /></div>
    <template v-else>
      <!-- Header -->
      <div class="card">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-xl font-bold text-white">{{ resume.original_filename }}</h1>
            <p class="text-slate-400 text-sm mt-1">Uploaded {{ new Date(resume.created_at).toLocaleDateString() }}</p>
            <StatusBadge :status="resume.parse_status" class="mt-2" />
          </div>
          <button class="btn btn-primary" @click="showOptimizeModal = true">
            ✨ Optimize for Job
          </button>
        </div>
      </div>

      <!-- Version list -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">Versions ({{ versions.length }})</h3>
        <div v-if="versions.length === 0" class="text-center py-8 text-slate-500">
          <p class="text-sm">No versions yet. Upload a resume to see the master version.</p>
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="ver in versions" :key="ver.id"
            class="p-4 rounded-xl glass border border-white/06 flex items-center justify-between gap-4"
          >
            <div class="min-w-0">
              <div class="flex items-center gap-2">
        <button v-if="!resume?.is_master" @click="handleSetMaster" class="btn btn-secondary btn-sm" title="Set as Master">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            Set Master
        </button>
        <button v-if="!resume?.is_master" @click="handleDelete" :disabled="isDeleting" class="btn btn-danger btn-sm" title="Delete Version">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Delete
        </button>
                <span class="font-medium text-white text-sm">{{ ver.label }}</span>
                <span v-if="ver.is_master" class="badge badge-blue text-[10px] py-0.5">Master</span>
              </div>
              <p v-if="ver.job_posting" class="text-slate-400 text-xs mt-0.5">
                Optimized for: {{ ver.job_posting.company_name }} – {{ ver.job_posting.job_title }}
              </p>
              <p class="text-slate-500 text-xs mt-0.5">{{ new Date(ver.created_at).toLocaleDateString() }}</p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
              <button v-if="!ver.is_master" class="btn btn-secondary btn-sm" @click="loadDiff(ver.id)">Compare</button>
              <a :href="downloadUrl(ver.id, 'docx')" class="btn btn-secondary btn-sm" target="_blank">DOCX</a>
              <a :href="downloadUrl(ver.id, 'pdf')" class="btn btn-secondary btn-sm" target="_blank">PDF</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Diff view -->
      <div v-if="showDiff" class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-white">Version Comparison</h3>
          <button class="btn btn-ghost btn-sm" @click="showDiff = false">✕ Close</button>
        </div>
        <div v-if="diffLoading" class="flex justify-center py-8"><LoadingSpinner :size="28" /></div>
        <div v-else-if="!diffData" class="text-center py-8 text-slate-500">No diff data available.</div>
        <div v-else class="grid lg:grid-cols-3 gap-4">
          <div class="lg:col-span-2 space-y-2">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Changes</p>
            <div
              v-for="(change, i) in diffData.change_log ?? []" :key="i"
              :class="['p-3 rounded-lg text-sm', change.change_type === 'added' ? 'diff-added' : change.change_type === 'removed' ? 'diff-removed' : 'diff-modified']"
            >
              <div class="font-medium text-xs text-slate-300 mb-1 uppercase">{{ change.section }} · {{ change.change_type }}</div>
              <div v-if="change.before" class="text-red-300 line-through text-xs mb-0.5">{{ change.before }}</div>
              <div v-if="change.after" class="text-green-300 text-xs">{{ change.after }}</div>
              <div v-if="change.reason" class="text-slate-400 text-xs mt-1 italic">{{ change.reason }}</div>
            </div>
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Summary</p>
            <div class="glass rounded-xl p-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-400">Total changes</span>
                <span class="text-white font-medium">{{ diffData.change_log?.length ?? 0 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Optimize modal -->
    <Modal v-if="showOptimizeModal" title="Optimize Resume for Job" @close="showOptimizeModal = false">
      <p class="text-slate-400 text-sm mb-4">Select the job posting to optimize your master resume for:</p>
      <div class="space-y-2 max-h-72 overflow-y-auto mb-4">
        <div
          v-for="job in jobsStore.jobPostings" :key="job.id"
          :class="['p-3 rounded-lg cursor-pointer border transition-all', selectedJobId === job.id ? 'border-blue-500 bg-blue-500/10' : 'border-white/06 glass-hover']"
          @click="selectedJobId = job.id"
        >
          <p class="font-medium text-white text-sm">{{ job.job_title }}</p>
          <p class="text-slate-400 text-xs">{{ job.company_name }}</p>
        </div>
      </div>
      <div class="flex gap-2 justify-end">
        <button class="btn btn-secondary" @click="showOptimizeModal = false">Cancel</button>
        <button class="btn btn-primary" :disabled="!selectedJobId || optimizing" @click="optimize">
          <LoadingSpinner v-if="optimizing" :size="14" />
          {{ optimizing ? 'Optimizing...' : 'Optimize Resume' }}
        </button>
      </div>
    </Modal>
  </div>
</template>
