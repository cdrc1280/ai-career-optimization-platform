<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useResumesStore } from '../stores/resumes'
import FileDropzone from '../components/ui/FileDropzone.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useRouter } from 'vue-router'
import { addToast } from '../composables/toast'
import PageLayout from '../components/layout/PageLayout.vue'

const resumesStore = useResumesStore()
const router = useRouter()
let pollTimer: any = null

const deleteModal = ref({ show: false, id: 0 })

onMounted(async () => {
    await resumesStore.fetch()
    startPolling()
})

function startPolling() {
    pollTimer = setInterval(async () => {
        const hasProcessing = resumesStore.resumes.some(r => ['pending','processing'].includes(r.parse_status))
        if (hasProcessing) await resumesStore.fetch()
    }, 4000)
}

async function handleFile(file: File) {
    try {
        await resumesStore.upload(file)
        addToast('success', 'Resume uploaded! Parsing in progress...')
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Upload failed.')
    }
}

function confirmDelete(id: number) {
    deleteModal.value = { show: true, id }
}

async function deleteResume() {
    await resumesStore.remove(deleteModal.value.id)
    deleteModal.value.show = false
    addToast('success', 'Resume deleted.')
}

function statusColor(s: string) {
    return { pending:'text-slate-400', processing:'text-blue-400', completed:'text-green-400', failed:'text-red-400', needs_review:'text-yellow-400' }[s] ?? 'text-slate-400'
}
</script>

<template>
  <PageLayout title="Resumes" subtitle="Upload and manage your resumes for AI optimization">

    <!-- Upload -->
    <div class="card">
      <h3 class="font-semibold text-white mb-4">Upload New Resume</h3>
      <FileDropzone v-if="!resumesStore.uploading" @file="handleFile" />
      <div v-else class="border-2 border-dashed border-blue-500/30 rounded-xl p-10 text-center">
        <LoadingSpinner class="mx-auto mb-3" :size="32" />
        <p class="text-slate-300 font-medium">Uploading resume...</p>
      </div>
    </div>

    <!-- Resume list -->
    <div class="card">
      <h3 class="font-semibold text-white mb-4">Your Resumes ({{ resumesStore.resumes.length }})</h3>
      <div v-if="resumesStore.loading && resumesStore.resumes.length === 0" class="py-8 flex justify-center">
        <LoadingSpinner :size="28" />
      </div>
      <div v-else-if="resumesStore.resumes.length === 0" class="text-center py-12 text-slate-500">
        <div class="text-4xl mb-3">📄</div>
        <p>No resumes uploaded yet. Drop your resume above to get started.</p>
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="resume in resumesStore.resumes" :key="resume.id"
          class="flex items-center justify-between p-4 rounded-xl glass-hover border border-white/05 cursor-pointer transition-all hover:border-blue-500/20"
          @click="router.push(`/resumes/${resume.id}`)"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(59,130,246,0.1)">
              <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="min-w-0">
              <p class="font-medium text-white text-sm truncate">{{ resume.original_filename }}</p>
              <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                <span>{{ new Date(resume.created_at).toLocaleDateString() }}</span>
                <span>·</span>
                <span :class="statusColor(resume.parse_status)">{{ resume.parse_status }}</span>
                <LoadingSpinner v-if="['pending','processing'].includes(resume.parse_status)" :size="10" />
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0">
            <StatusBadge :status="resume.parse_status" />
            <button class="btn btn-secondary btn-sm" @click.stop="router.push(`/resumes/${resume.id}`)">Open →</button>
            <button class="btn btn-danger btn-sm" @click.stop="confirmDelete(resume.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>
    <ConfirmModal 
      :show="deleteModal.show" 
      title="Delete Resume"
      message="Are you sure you want to delete this resume? This action cannot be undone."
      @close="deleteModal.show = false"
      @confirm="deleteResume"
    />
  </PageLayout>
</template>
