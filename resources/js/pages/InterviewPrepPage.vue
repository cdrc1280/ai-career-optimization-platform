<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAnalysisStore } from '../stores/analysis'
import { useRouter } from 'vue-router'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'

const analysisStore = useAnalysisStore()
const router = useRouter()
const activeTab = ref<'behavioral' | 'technical' | 'company_specific' | 'role_specific'>('behavioral')
const practiceMode = ref(false)
const practiceIndex = ref(0)
const expandedQuestions = ref<Set<number>>(new Set())

onMounted(async () => {
    await analysisStore.fetchAll()
    if (analysisStore.analyses.length > 0) analysisStore.current = analysisStore.analyses[0]
})

const interviewPrep = computed(() => analysisStore.current?.interview_prep ?? null)
const currentQuestions = computed(() => {
    if (!interviewPrep.value) return []
    return interviewPrep.value[activeTab.value] ?? []
})

function toggleExpand(i: number) {
    if (expandedQuestions.value.has(i)) expandedQuestions.value.delete(i)
    else expandedQuestions.value.add(i)
}

const tabLabels = [
    { key: 'behavioral',      label: '🎭 Behavioral',        desc: 'Situational and behavioral questions' },
    { key: 'technical',       label: '⚙️ Technical',         desc: 'Role-specific technical questions' },
    { key: 'company_specific', label: '🏢 Company-Specific', desc: 'Questions about the company' },
    { key: 'role_specific',   label: '💼 Role-Specific',     desc: 'Questions specific to the role' },
]
</script>

<template>
  <div class="fade-in space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Interview Prep</h1>
        <p class="text-slate-400 text-sm mt-0.5">AI-generated questions and suggested answers based on your profile</p>
      </div>
      <label class="flex items-center gap-2 cursor-pointer">
        <span class="text-sm text-slate-400">Practice Mode</span>
        <div :class="['w-10 h-5 rounded-full transition-colors relative', practiceMode ? 'bg-blue-500' : 'bg-white/10']"
          @click="practiceMode = !practiceMode; practiceIndex = 0; expandedQuestions.clear()">
          <div :class="['absolute top-0.5 w-4 h-4 rounded-full bg-white transition-transform', practiceMode ? 'translate-x-5' : 'translate-x-0.5']" />
        </div>
      </label>
    </div>

    <div v-if="!interviewPrep" class="card text-center py-16">
      <div class="text-5xl mb-4">🎤</div>
      <h3 class="text-lg font-semibold text-white mb-2">No Interview Prep Available</h3>
      <p class="text-slate-400 text-sm mb-6">Run an analysis first to get personalized interview questions based on your experience and the job requirements.</p>
      <button class="btn btn-primary" @click="router.push('/job-postings')">Run Analysis →</button>
    </div>

    <template v-else>
      <!-- Analysis selector -->
      <div v-if="analysisStore.analyses.length > 1" class="card">
        <p class="text-sm text-slate-400 mb-2">Select analysis:</p>
        <div class="flex gap-2 overflow-x-auto pb-1">
          <button v-for="a in analysisStore.analyses" :key="a.id"
            :class="['btn btn-sm flex-shrink-0', analysisStore.current?.id === a.id ? 'btn-primary' : 'btn-secondary']"
            @click="analysisStore.current = a">
            {{ a.job_posting?.company_name ?? 'Job' }}
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tab-bar">
        <button v-for="tab in tabLabels" :key="tab.key"
          :class="['tab-item', activeTab === tab.key && 'active']"
          @click="activeTab = tab.key as any; practiceIndex = 0; expandedQuestions.clear()">
          {{ tab.label }}
        </button>
      </div>

      <!-- Practice mode -->
      <div v-if="practiceMode && currentQuestions.length > 0" class="card">
        <div class="text-center mb-4">
          <p class="text-slate-400 text-sm">Question {{ practiceIndex + 1 }} of {{ currentQuestions.length }}</p>
          <div class="progress-track mt-2">
            <div class="progress-fill" :style="`width: ${((practiceIndex + 1) / currentQuestions.length) * 100}%; background: linear-gradient(90deg,#3b82f6,#6366f1)`" />
          </div>
        </div>
        <h3 class="text-lg font-semibold text-white text-center mb-6">{{ currentQuestions[practiceIndex]?.question }}</h3>
        <div class="flex gap-3 justify-center">
          <button class="btn btn-secondary" :disabled="practiceIndex === 0" @click="practiceIndex--">← Previous</button>
          <button class="btn btn-primary" @click="expandedQuestions.has(practiceIndex) ? expandedQuestions.delete(practiceIndex) : expandedQuestions.add(practiceIndex)">
            {{ expandedQuestions.has(practiceIndex) ? 'Hide Answer' : '💡 Show Answer' }}
          </button>
          <button class="btn btn-secondary" :disabled="practiceIndex >= currentQuestions.length - 1" @click="practiceIndex++">Next →</button>
        </div>
        <div v-if="expandedQuestions.has(practiceIndex)" class="mt-4 p-4 rounded-xl bg-blue-500/5 border border-blue-500/15">
          <p class="text-slate-300 text-sm leading-relaxed">{{ currentQuestions[practiceIndex]?.suggested_answer }}</p>
        </div>
      </div>

      <!-- Q&A accordion (non-practice mode) -->
      <div v-else class="space-y-3">
        <div v-if="currentQuestions.length === 0" class="card text-center py-8 text-slate-500">
          No questions available for this category.
        </div>
        <div v-for="(qa, i) in currentQuestions" :key="i" class="card glass-hover cursor-pointer" @click="toggleExpand(i)">
          <div class="flex items-start gap-3">
            <span class="text-blue-400 font-bold text-sm flex-shrink-0 mt-0.5">Q{{ i + 1 }}</span>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-white text-sm">{{ qa.question }}</p>
              <div v-if="expandedQuestions.has(i)" class="mt-3 pt-3 border-t border-white/06">
                <p class="text-xs text-slate-400 mb-1 uppercase tracking-wider font-semibold">Suggested Answer</p>
                <p class="text-slate-300 text-sm leading-relaxed">{{ qa.suggested_answer }}</p>
              </div>
            </div>
            <span class="text-slate-500 text-sm flex-shrink-0 transition-transform" :class="expandedQuestions.has(i) ? 'rotate-180' : ''">▼</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
