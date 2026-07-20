<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAnalysisStore } from '../stores/analysis'
import { useRouter } from 'vue-router'

const analysisStore = useAnalysisStore()
const router = useRouter()

onMounted(async () => {
    await analysisStore.fetchAll()
    if (analysisStore.analyses.length > 0) analysisStore.current = analysisStore.analyses[0]
})

const recs = computed(() => analysisStore.current?.career_recommendations ?? null)
</script>

<template>
  <div class="fade-in space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-white">Career Recommendations</h1>
      <p class="text-slate-400 text-sm mt-0.5">Personalized roadmap to accelerate your career growth</p>
    </div>

    <div v-if="!recs" class="card text-center py-16">
      <div class="text-5xl mb-4">🧭</div>
      <h3 class="text-lg font-semibold text-white mb-2">No Recommendations Yet</h3>
      <p class="text-slate-400 text-sm mb-6">Run a resume analysis to get personalized career recommendations.</p>
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

      <!-- Certifications -->
      <div v-if="recs.certifications?.length" class="card">
        <h3 class="font-semibold text-white mb-4">🏆 Recommended Certifications</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div v-for="cert in recs.certifications" :key="cert.name" class="p-4 rounded-xl glass border border-white/06 hover:border-yellow-500/30 transition-all">
            <div class="text-yellow-400 text-lg mb-1">🎓</div>
            <h4 class="font-semibold text-white text-sm mb-1">{{ cert.name }}</h4>
            <p class="text-blue-400 text-xs mb-2">{{ cert.provider }}</p>
            <p class="text-slate-400 text-xs">{{ cert.relevance }}</p>
          </div>
        </div>
      </div>

      <!-- Courses -->
      <div v-if="recs.courses?.length" class="card">
        <h3 class="font-semibold text-white mb-4">📚 Recommended Courses</h3>
        <div class="space-y-3">
          <div v-for="course in recs.courses" :key="course.title" class="flex items-center justify-between p-4 rounded-xl glass border border-white/06 hover:border-blue-500/30 transition-all">
            <div class="min-w-0">
              <h4 class="font-medium text-white text-sm">{{ course.title }}</h4>
              <p class="text-slate-400 text-xs mt-0.5">{{ course.platform }}</p>
            </div>
            <a v-if="course.url" :href="course.url" target="_blank" class="btn btn-secondary btn-sm flex-shrink-0 ml-3">
              Open →
            </a>
          </div>
        </div>
      </div>

      <!-- Career Paths -->
      <div v-if="recs.career_paths?.length" class="card">
        <h3 class="font-semibold text-white mb-4">🗺️ Career Paths</h3>
        <div class="space-y-2">
          <div v-for="(path, i) in recs.career_paths" :key="i" class="flex items-center gap-3 p-3 rounded-lg glass border border-white/06">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: linear-gradient(135deg,#3b82f6,#6366f1)">
              {{ i + 1 }}
            </div>
            <p class="text-slate-300 text-sm">{{ path }}</p>
          </div>
        </div>
      </div>

      <!-- Portfolio Projects -->
      <div v-if="recs.portfolio_projects?.length" class="card">
        <h3 class="font-semibold text-white mb-4">🛠️ Portfolio Project Ideas</h3>
        <div class="grid sm:grid-cols-2 gap-3">
          <div v-for="(proj, i) in recs.portfolio_projects" :key="i" class="p-4 rounded-xl glass border border-white/06 hover:border-indigo-500/30 transition-all">
            <div class="text-indigo-400 text-lg mb-2">💡</div>
            <p class="text-slate-300 text-sm leading-relaxed">{{ proj }}</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
