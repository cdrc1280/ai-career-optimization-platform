<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAnalysisStore } from '../stores/analysis'
import { useResumesStore } from '../stores/resumes'
import { useJobPostingsStore } from '../stores/jobPostings'
import ScoreGauge from '../components/ui/ScoreGauge.vue'
import ProgressBar from '../components/ui/ProgressBar.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import Modal from '../components/ui/Modal.vue'
import { generateCoverLetter } from '../services/api'
import { addToast } from '../composables/toast'
import { useRouter } from 'vue-router'

const analysisStore = useAnalysisStore()
const resumesStore = useResumesStore()
const jobsStore = useJobPostingsStore()
const router = useRouter()

const selectedVersionId = ref<number | null>(null)
const selectedJobId = ref<number | null>(null)
const showCoverLetterModal = ref(false)
const selectedTone = ref('professional')
const generatingCL = ref(false)

onMounted(async () => {
    await Promise.all([analysisStore.fetchAll(), resumesStore.fetch(), jobsStore.fetch()])
    if (analysisStore.analyses.length > 0) {
        const latest = analysisStore.analyses[0]
        analysisStore.current = latest
    }
})

const analysis = computed(() => analysisStore.current)

const scoreBars = computed(() => {
    if (!analysis.value) return []
    const a = analysis.value
    return [
        { label: 'Skills Match',         value: a.skills_match_score,       key: 'skills' },
        { label: 'Experience Match',     value: a.experience_match_score,   key: 'experience' },
        { label: 'Education Match',      value: a.education_match_score,    key: 'education' },
        { label: 'ATS Compatibility',    value: a.ats_compatibility_score,  key: 'ats' },
        { label: 'Keyword Coverage',     value: a.keyword_coverage_score,   key: 'keywords' },
        { label: 'Industry Alignment',   value: a.industry_alignment_score, key: 'industry' },
    ]
})

function barColor(v: number) {
    if (v >= 80) return 'success'
    if (v >= 60) return 'warning'
    return 'danger'
}

async function selectAnalysis(a: any) {
    analysisStore.current = a
}

async function generateCL() {
    if (!analysis.value) return
    generatingCL.value = true
    try {
        await generateCoverLetter({
            resume_version_id: analysis.value.resume_version_id,
            job_posting_id: analysis.value.job_posting_id,
            tone: selectedTone.value,
        })
        addToast('success', 'Cover letter generated! View in Cover Letters tab.')
        showCoverLetterModal.value = false
        router.push('/cover-letters')
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Generation failed.')
    } finally { generatingCL.value = false }
}
</script>

<template>
  <div class="fade-in space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Resume Analysis</h1>
        <p class="text-slate-400 text-sm mt-0.5">AI-powered match scoring and keyword analysis</p>
      </div>
    </div>

    <!-- Analysis selector -->
    <div v-if="analysisStore.analyses.length > 0" class="card">
      <h3 class="font-semibold text-white mb-3 text-sm">Select Analysis</h3>
      <div class="flex gap-2 overflow-x-auto pb-1">
        <button
          v-for="a in analysisStore.analyses" :key="a.id"
          :class="['btn btn-sm flex-shrink-0 transition-all', analysisStore.current?.id === a.id ? 'btn-primary' : 'btn-secondary']"
          @click="selectAnalysis(a)"
        >
          {{ a.resume_version?.label ?? 'Resume' }} vs {{ a.job_posting?.company_name ?? 'Job' }}
        </button>
      </div>
    </div>

    <!-- No analysis -->
    <div v-if="!analysis" class="card text-center py-16">
      <div class="text-5xl mb-4">🎯</div>
      <h3 class="text-lg font-semibold text-white mb-2">No Analysis Yet</h3>
      <p class="text-slate-400 text-sm mb-6">Go to Job Postings and run an analysis to see your match score.</p>
      <button class="btn btn-primary" @click="router.push('/job-postings')">Add Job Posting →</button>
    </div>

    <template v-else>
      <!-- Score overview -->
      <div class="card">
        <div class="flex flex-col sm:flex-row items-center gap-8">
          <div class="relative flex items-center justify-center" style="width:140px;height:140px">
            <svg width="140" height="140" viewBox="0 0 100 100" class="-rotate-90" style="position:absolute">
              <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
              <circle cx="50" cy="50" r="42" fill="none"
                :stroke="analysis.overall_match_score >= 80 ? '#10b981' : analysis.overall_match_score >= 60 ? '#f59e0b' : '#ef4444'"
                stroke-width="8" stroke-linecap="round"
                :stroke-dasharray="`${2 * Math.PI * 42}`"
                :stroke-dashoffset="`${2 * Math.PI * 42 * (1 - analysis.overall_match_score / 100)}`"
                style="transition: stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1)"
              />
            </svg>
            <div class="text-center z-10">
              <div class="text-3xl font-black text-white">{{ analysis.overall_match_score }}</div>
              <div class="text-xs text-slate-400">/100</div>
            </div>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-white mb-1">Overall Match Score</h3>
            <p class="text-slate-400 text-sm mb-4">{{ analysis.score_explanations?.overall }}</p>
            <div class="flex gap-2">
              <button class="btn btn-primary btn-sm" @click="showCoverLetterModal = true">✉️ Generate Cover Letter</button>
              <button class="btn btn-secondary btn-sm" @click="router.push('/resumes')">✨ Optimize Resume</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Score breakdown -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">Score Breakdown</h3>
        <div class="space-y-4">
          <div v-for="bar in scoreBars" :key="bar.key" class="space-y-1.5">
            <div class="flex items-center justify-between text-sm">
              <span class="text-slate-300 font-medium">{{ bar.label }}</span>
              <span :class="['font-bold', bar.value >= 80 ? 'text-green-400' : bar.value >= 60 ? 'text-yellow-400' : 'text-red-400']">
                {{ bar.value ?? 0 }}%
              </span>
            </div>
            <ProgressBar :value="bar.value ?? 0" :color="barColor(bar.value ?? 0)" />
            <p v-if="analysis.score_explanations?.[bar.key]" class="text-slate-500 text-xs">
              {{ analysis.score_explanations[bar.key] }}
            </p>
          </div>
        </div>
      </div>

      <!-- Keywords -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">Keyword Analysis</h3>
        <div class="space-y-4">
          <div v-if="analysis.present_keywords?.length">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">✅ Present Keywords</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="k in analysis.present_keywords" :key="k" class="badge badge-green">{{ k }}</span>
            </div>
          </div>
          <div v-if="analysis.missing_keywords?.length">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">❌ Missing Keywords</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="k in analysis.missing_keywords" :key="k" class="badge badge-red">{{ k }}</span>
            </div>
          </div>
          <div v-if="analysis.recommended_keywords?.length">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">💡 Recommended Keywords</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="k in analysis.recommended_keywords" :key="k" class="badge badge-blue">{{ k }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ATS Issues -->
      <div v-if="analysis.ats_issues?.length" class="card">
        <h3 class="font-semibold text-white mb-4">⚠️ ATS Compatibility Issues</h3>
        <div class="space-y-2">
          <div v-for="(issue, i) in analysis.ats_issues" :key="i" class="flex gap-3 p-3 rounded-lg bg-yellow-500/5 border border-yellow-500/15">
            <span class="text-yellow-400 text-sm flex-shrink-0">⚠️</span>
            <p class="text-slate-300 text-sm">{{ issue }}</p>
          </div>
        </div>
      </div>

      <!-- Skill Gap Details -->
      <div v-if="analysis.skill_gap_details?.length" class="card">
        <h3 class="font-semibold text-white mb-4">🔍 Skill Gap Analysis</h3>
        <div class="space-y-4">
          <div v-for="gap in analysis.skill_gap_details" :key="gap.skill" class="p-4 rounded-xl glass border border-white/06">
            <div class="flex items-start gap-3">
              <div class="badge badge-red flex-shrink-0 mt-0.5">Missing</div>
              <div class="flex-1">
                <h4 class="font-semibold text-white">{{ gap.skill }}</h4>
                <p class="text-slate-400 text-sm mt-1">{{ gap.why_it_matters }}</p>
                <p v-if="gap.where_in_jd" class="text-slate-500 text-xs mt-1">
                  <span class="text-indigo-400">In JD:</span> {{ gap.where_in_jd }}
                </p>
                <div v-if="gap.learning_resources?.length" class="mt-2">
                  <p class="text-xs text-slate-400 mb-1">Learning resources:</p>
                  <div class="flex flex-wrap gap-2">
                    <a v-for="url in gap.learning_resources" :key="url" :href="url" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 underline transition-colors">
                      {{ new URL(url).hostname.replace('www.', '') }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Cover letter modal -->
    <Modal v-if="showCoverLetterModal" title="Generate Cover Letter" @close="showCoverLetterModal = false">
      <p class="text-slate-400 text-sm mb-4">Select the tone for your cover letter:</p>
      <div class="grid grid-cols-2 gap-2 mb-4">
        <button
          v-for="tone in [{v:'professional',l:'Professional',d:'Formal and polished'},{v:'friendly',l:'Friendly',d:'Warm and approachable'},{v:'executive',l:'Executive',d:'High-level authority'},{v:'technical',l:'Technical',d:'Detail-oriented focus'}]"
          :key="tone.v"
          :class="['p-3 rounded-xl border text-left transition-all', selectedTone === tone.v ? 'border-blue-500 bg-blue-500/10' : 'border-white/06 glass-hover']"
          @click="selectedTone = tone.v"
        >
          <div class="font-medium text-white text-sm">{{ tone.l }}</div>
          <div class="text-slate-400 text-xs">{{ tone.d }}</div>
        </button>
      </div>
      <div class="flex gap-2 justify-end">
        <button class="btn btn-secondary" @click="showCoverLetterModal = false">Cancel</button>
        <button class="btn btn-primary" :disabled="generatingCL" @click="generateCL">
          <LoadingSpinner v-if="generatingCL" :size="14" />
          {{ generatingCL ? 'Generating...' : 'Generate' }}
        </button>
      </div>
    </Modal>
  </div>
</template>
