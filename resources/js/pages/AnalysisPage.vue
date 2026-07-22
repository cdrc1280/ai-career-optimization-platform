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
import StatusBadge from '../components/ui/StatusBadge.vue'
import { addToast } from '../composables/toast'
import PageLayout from '../components/layout/PageLayout.vue'
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
  <PageLayout title="Resume Analysis" subtitle="AI feedback on how well your resume matches your target jobs">
    <div v-if="analysisStore.loading && analysisStore.analyses.length === 0" class="flex justify-center py-12">
      <LoadingSpinner />
    </div>

    <!-- Analysis selector -->
    <div v-else-if="analysisStore.analyses.length > 0" class="card">
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
    <div v-if="!analysis && !analysisStore.loading" class="card text-center py-16">
      <div class="text-5xl mb-4">🎯</div>
      <h3 class="text-lg font-semibold text-white mb-2">No Analysis Yet</h3>
      <p class="text-slate-400 text-sm mb-6">Go to Job Postings and run an analysis to see your match score.</p>
      <button class="btn btn-primary" @click="router.push('/job-postings')">Add Job Posting →</button>
    </div>

    <template v-else-if="analysis">
      <!-- Score overview -->
      <div class="card mt-6">
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

      <!-- Integrity Flags -->
      <div v-if="analysis.integrity_flags && (analysis.integrity_flags.unsupported_skills?.length || analysis.integrity_flags.experience_inconsistencies?.length || analysis.integrity_flags.hallucination_warnings?.length)" class="card border-red-500/30 bg-red-500/5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          Integrity & Verification Flags
        </h3>
        
        <div class="space-y-4">
          <div v-if="analysis.integrity_flags.unsupported_skills?.length">
            <h4 class="text-sm font-medium text-red-400 mb-2">Unsupported Skills Found</h4>
            <ul class="list-disc list-inside text-sm text-slate-300 space-y-1">
              <li v-for="skill in analysis.integrity_flags.unsupported_skills" :key="skill">{{ skill }}</li>
            </ul>
          </div>
          <div v-if="analysis.integrity_flags.experience_inconsistencies?.length">
            <h4 class="text-sm font-medium text-red-400 mb-2">Experience Inconsistencies</h4>
            <ul class="list-disc list-inside text-sm text-slate-300 space-y-1">
              <li v-for="inc in analysis.integrity_flags.experience_inconsistencies" :key="inc">{{ inc }}</li>
            </ul>
          </div>
          <div v-if="analysis.integrity_flags.hallucination_warnings?.length">
            <h4 class="text-sm font-medium text-red-400 mb-2">Possible AI Hallucinations</h4>
            <ul class="list-disc list-inside text-sm text-slate-300 space-y-1">
              <li v-for="warn in analysis.integrity_flags.hallucination_warnings" :key="warn">{{ warn }}</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Recruiter Simulator -->
      <div v-if="analysis.recruiter_review" class="card relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
          <svg class="w-24 h-24 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h3 class="font-semibold text-white mb-2 flex items-center gap-2">
          <span class="text-xl">👔</span> Recruiter Simulator
        </h3>
        <p class="text-slate-400 text-sm mb-6">{{ analysis.recruiter_review.summary }}</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Strengths & Weaknesses -->
          <div class="space-y-4">
            <div>
              <h4 class="text-sm font-medium text-green-400 mb-2 flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Strengths</h4>
              <ul class="text-sm text-slate-300 space-y-1.5 list-disc list-inside">
                <li v-for="str in analysis.recruiter_review.strengths" :key="str">{{ str }}</li>
              </ul>
            </div>
            <div v-if="analysis.recruiter_review.weaknesses?.length">
              <h4 class="text-sm font-medium text-yellow-400 mb-2 flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Weaknesses</h4>
              <ul class="text-sm text-slate-300 space-y-1.5 list-disc list-inside">
                <li v-for="weak in analysis.recruiter_review.weaknesses" :key="weak">{{ weak }}</li>
              </ul>
            </div>
          </div>
          
          <!-- Red Flags & Score -->
          <div class="space-y-6">
            <div v-if="analysis.recruiter_review.red_flags?.length">
              <h4 class="text-sm font-medium text-red-400 mb-2 flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg> Red Flags</h4>
              <ul class="text-sm text-slate-300 space-y-1.5 list-disc list-inside">
                <li v-for="flag in analysis.recruiter_review.red_flags" :key="flag">{{ flag }}</li>
              </ul>
            </div>
            
            <div class="glass p-4 rounded-xl border border-white/06">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-slate-400">Recruiter Impression</span>
                <span class="font-bold text-white">{{ analysis.recruiter_review.overall_score }}/100</span>
              </div>
              <ProgressBar :value="analysis.recruiter_review.overall_score" :color="analysis.recruiter_review.overall_score >= 80 ? 'bg-green-500' : analysis.recruiter_review.overall_score >= 60 ? 'bg-yellow-500' : 'bg-red-500'" />
              
              <div class="flex items-center justify-between mt-4">
                <span class="text-sm text-slate-400">ATS Pass Probability</span>
                <span class="font-bold text-white">{{ analysis.recruiter_review.ats_pass_probability }}%</span>
              </div>
              <ProgressBar class="mt-2" :value="analysis.recruiter_review.ats_pass_probability" :color="analysis.recruiter_review.ats_pass_probability >= 80 ? 'bg-blue-500' : analysis.recruiter_review.ats_pass_probability >= 60 ? 'bg-indigo-500' : 'bg-purple-500'" />
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
  </PageLayout>
</template>
