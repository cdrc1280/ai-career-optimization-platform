<template>
  <PageLayout title="Personal Branding" subtitle="AI-generated LinkedIn headlines, about sections, posts, and elevator pitches">
    <template #actions>
      <button class="btn btn-primary" @click="generateBrand" :disabled="generating">
        <span v-if="generating" class="animate-spin mr-2">⏳</span>
        {{ generating ? 'Generating Brand Assets...' : '✨ Generate Brand Content' }}
      </button>
    </template>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="!brand" class="text-center py-16 card mt-6">
      <div class="text-6xl mb-4">✨</div>
      <h3 class="text-xl font-bold text-white mb-2">No Personal Brand Generated Yet</h3>
      <p class="text-slate-400 mb-6 max-w-md mx-auto">Generate authentic LinkedIn headlines, professional bios, elevator pitches, and engagement posts based on your profile.</p>
      <button class="btn btn-primary" @click="generateBrand">Generate Personal Brand</button>
    </div>

    <div v-else class="space-y-6 mt-6">
      <!-- Headline & Pitch -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card relative overflow-hidden">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-white flex items-center gap-2">
              <span class="text-blue-400">💼</span> LinkedIn Headline
            </h3>
            <button @click="copyText(brand.headline)" class="text-xs text-blue-400 hover:underline">Copy</button>
          </div>
          <p class="text-slate-200 font-medium text-base glass p-4 rounded-xl border border-white/05 bg-slate-900/50">
            {{ brand.headline }}
          </p>
        </div>

        <div class="card relative overflow-hidden">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-white flex items-center gap-2">
              <span class="text-yellow-400">⚡</span> 30-Second Elevator Pitch
            </h3>
            <button @click="copyText(brand.elevator_pitch)" class="text-xs text-blue-400 hover:underline">Copy</button>
          </div>
          <p class="text-slate-300 text-sm leading-relaxed glass p-4 rounded-xl border border-white/05 bg-slate-900/50">
            {{ brand.elevator_pitch }}
          </p>
        </div>
      </div>

      <!-- About Section -->
      <div class="card">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-white flex items-center gap-2">
            <span class="text-indigo-400">📝</span> LinkedIn About Section
          </h3>
          <button @click="copyText(brand.about_section)" class="text-xs text-blue-400 hover:underline">Copy</button>
        </div>
        <div class="prose prose-invert prose-sm max-w-none text-slate-300 glass p-5 rounded-xl border border-white/05 bg-slate-900/50 whitespace-pre-wrap">
          {{ brand.about_section }}
        </div>
      </div>

      <!-- GitHub Bio & Portfolio Landing -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-white flex items-center gap-2">
              <span class="text-purple-400">🐙</span> GitHub Profile Bio
            </h3>
            <button @click="copyText(brand.github_bio)" class="text-xs text-blue-400 hover:underline">Copy</button>
          </div>
          <p class="text-slate-300 text-sm glass p-4 rounded-xl border border-white/05 bg-slate-900/50 font-mono">
            {{ brand.github_bio }}
          </p>
        </div>

        <div v-if="brand.portfolio_copy" class="card">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-white flex items-center gap-2">
              <span class="text-emerald-400">🌐</span> Portfolio Hero Subtitle
            </h3>
            <button @click="copyText(brand.portfolio_copy.hero_subtitle)" class="text-xs text-blue-400 hover:underline">Copy</button>
          </div>
          <p class="text-slate-300 text-sm glass p-4 rounded-xl border border-white/05 bg-slate-900/50">
            {{ brand.portfolio_copy.hero_subtitle }}
          </p>
        </div>
      </div>

      <!-- Sample LinkedIn Posts -->
      <div v-if="brand.linkedin_posts?.length" class="card">
        <h3 class="font-bold text-white mb-4 flex items-center gap-2">
          <span class="text-blue-400">🚀</span> Suggested LinkedIn Posts
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="(post, idx) in brand.linkedin_posts" :key="idx" class="glass p-4 rounded-xl border border-white/05 flex flex-col justify-between">
            <p class="text-xs text-slate-300 whitespace-pre-wrap mb-4">{{ post }}</p>
            <button @click="copyText(post)" class="btn btn-secondary btn-sm w-full">Copy Post</button>
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import api from '../services/api'

const brand = ref<any>(null)
const loading = ref(true)
const generating = ref(false)

onMounted(async () => {
  await fetchBrand()
})

async function fetchBrand() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/personal-branding')
    brand.value = res.data
  } catch (e) {
    console.error('Failed to fetch brand data', e)
  } finally {
    loading.value = false
  }
}

async function generateBrand() {
  generating.value = true
  try {
    const res = await api.post('/api/v1/personal-branding/generate')
    brand.value = res.data
  } catch (e) {
    alert('Failed to generate personal brand')
  } finally {
    generating.value = false
  }
}

function copyText(text: string) {
  if (!text) return
  navigator.clipboard.writeText(text)
  alert('Copied to clipboard!')
}
</script>
