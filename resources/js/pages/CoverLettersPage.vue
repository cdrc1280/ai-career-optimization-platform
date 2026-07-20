<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { getCoverLetters, generateCoverLetter , updateCoverLetter, exportCoverLetterUrl} from '../services/api'
import { useResumesStore } from '../stores/resumes'
import { useJobPostingsStore } from '../stores/jobPostings'
import Modal from '../components/ui/Modal.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import { addToast } from '../composables/toast'

const resumesStore = useResumesStore()
const jobsStore = useJobPostingsStore()
const letters = ref<any[]>([])
const loading = ref(false)
const showModal = ref(false)
const step = ref(1) // 1=select resume, 2=select job, 3=tone, 4=result
const selectedVersionId = ref<number | null>(null)
const selectedJobId = ref<number | null>(null)
const selectedTone = ref('professional')
const generatedText = ref('')
const generating = ref(false)
const editedText = ref('')

onMounted(async () => {
    loading.value = true
    try {
        const res = await getCoverLetters()
        letters.value = res.data?.data ?? res.data ?? []
    } finally { loading.value = false }
    await Promise.all([resumesStore.fetch(), jobsStore.fetch()])
})

const allVersions = computed(() => {
    const vers: any[] = []
    resumesStore.resumes.forEach(r => {
        if (r.master_version) {
            vers.push({ ...r.master_version, resume_name: r.original_filename })
        }
    })
    return vers
})

function openWizard() {
    step.value = 1; selectedVersionId.value = null; selectedJobId.value = null
    selectedTone.value = 'professional'; generatedText.value = ''; editedText.value = ''
    showModal.value = true
}

async function generate() {
    if (!selectedVersionId.value || !selectedJobId.value) return
    generating.value = true
    step.value = 4
    try {
        const res = await generateCoverLetter({ resume_version_id: selectedVersionId.value, job_posting_id: selectedJobId.value, tone: selectedTone.value })
        generatedText.value = res.data?.content ?? ''
        editedText.value = generatedText.value
        const res2 = await getCoverLetters()
        letters.value = res2.data?.data ?? res2.data ?? []
        addToast('success', 'Cover letter generated successfully!')
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Generation failed.')
        step.value = 3
    } finally { generating.value = false }
}

const tones = [
    { v: 'professional', l: '🎩 Professional', d: 'Formal and polished' },
    { v: 'friendly',     l: '😊 Friendly',     d: 'Warm and approachable' },
    { v: 'executive',    l: '💼 Executive',     d: 'High-level authority' },
    { v: 'technical',    l: '⚙️ Technical',    d: 'Detail-oriented focus' },
]
</script>

<template>
  <div class="fade-in space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Cover Letters</h1>
        <p class="text-slate-400 text-sm mt-0.5">AI-generated cover letters tailored to each job</p>
      </div>
      <button class="btn btn-primary" @click="openWizard">✉️ Generate New</button>
    </div>

    <div v-if="loading" class="flex justify-center py-12"><LoadingSpinner :size="32" /></div>
    <div v-else-if="letters.length === 0" class="card text-center py-16">
      <div class="text-5xl mb-4">✉️</div>
      <h3 class="text-lg font-semibold text-white mb-2">No Cover Letters Yet</h3>
      <p class="text-slate-400 text-sm mb-6">Generate your first AI cover letter tailored to a specific job posting.</p>
      <button class="btn btn-primary" @click="openWizard">Generate Cover Letter</button>
    </div>
    <div v-else class="space-y-4">
      <div v-for="letter in letters" :key="letter.id" class="card glass-hover">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <h4 class="font-semibold text-white">{{ letter.job_posting?.job_title ?? 'Cover Letter' }}</h4>
            <p class="text-slate-400 text-sm">{{ letter.job_posting?.company_name }}</p>
            <div class="flex items-center gap-2 mt-2">
              <StatusBadge :status="letter.tone" />
              <span class="text-slate-500 text-xs">{{ new Date(letter.created_at).toLocaleDateString() }}</span>
            </div>
            <p class="text-slate-500 text-sm mt-3 line-clamp-2">{{ letter.content?.substring(0, 140) }}...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Wizard modal -->
    <Modal v-if="showModal" :title="step === 4 ? 'Generated Cover Letter' : 'Generate Cover Letter'" max-width="680px" @close="showModal = false">
      <!-- Step 1: Select resume -->
      <template v-if="step === 1">
        <p class="text-slate-400 text-sm mb-3">Step 1/3 — Select resume version</p>
        <div class="space-y-2 max-h-64 overflow-y-auto mb-4">
          <div v-for="ver in allVersions" :key="ver.id"
            :class="['p-3 rounded-lg cursor-pointer border transition-all', selectedVersionId === ver.id ? 'border-blue-500 bg-blue-500/10' : 'border-white/06 glass-hover']"
            @click="selectedVersionId = ver.id">
            <p class="font-medium text-white text-sm">{{ ver.resume_name }}</p>
            <p class="text-slate-400 text-xs">{{ ver.label }}</p>
          </div>
        </div>
        <div class="flex justify-end"><button class="btn btn-primary" :disabled="!selectedVersionId" @click="step = 2">Next →</button></div>
      </template>

      <!-- Step 2: Select job -->
      <template v-else-if="step === 2">
        <p class="text-slate-400 text-sm mb-3">Step 2/3 — Select job posting</p>
        <div class="space-y-2 max-h-64 overflow-y-auto mb-4">
          <div v-for="job in jobsStore.jobPostings" :key="job.id"
            :class="['p-3 rounded-lg cursor-pointer border transition-all', selectedJobId === job.id ? 'border-blue-500 bg-blue-500/10' : 'border-white/06 glass-hover']"
            @click="selectedJobId = job.id">
            <p class="font-medium text-white text-sm">{{ job.job_title }}</p>
            <p class="text-slate-400 text-xs">{{ job.company_name }}</p>
          </div>
        </div>
        <div class="flex gap-2 justify-end">
          <button class="btn btn-secondary" @click="step = 1">← Back</button>
          <button class="btn btn-primary" :disabled="!selectedJobId" @click="step = 3">Next →</button>
        </div>
      </template>

      <!-- Step 3: Tone -->
      <template v-else-if="step === 3">
        <p class="text-slate-400 text-sm mb-3">Step 3/3 — Choose tone</p>
        <div class="grid grid-cols-2 gap-2 mb-4">
          <button v-for="tone in tones" :key="tone.v"
            :class="['p-3 rounded-xl border text-left transition-all', selectedTone === tone.v ? 'border-blue-500 bg-blue-500/10' : 'border-white/06 glass-hover']"
            @click="selectedTone = tone.v">
            <div class="font-medium text-white text-sm">{{ tone.l }}</div>
            <div class="text-slate-400 text-xs">{{ tone.d }}</div>
          </button>
        </div>
        <div class="flex gap-2 justify-end">
          <button class="btn btn-secondary" @click="step = 2">← Back</button>
          <button class="btn btn-primary" @click="generate">✨ Generate</button>
        </div>
      </template>

      <!-- Step 4: Result -->
      <template v-else-if="step === 4">
        <div v-if="generating" class="py-12 flex flex-col items-center gap-3">
          <LoadingSpinner :size="36" />
          <p class="text-slate-400 text-sm">AI is writing your cover letter...</p>
        </div>
        <div v-else>
          <textarea v-model="editedText" class="input w-full mb-4" rows="16" />
          <div class="flex gap-2 justify-end">
            <button class="btn btn-secondary" @click="showModal = false">Done</button>
            <button class="btn btn-primary" @click="step = 3">✨ Regenerate</button>
          </div>
        </div>
      </template>
    </Modal>
  </div>
</template>
