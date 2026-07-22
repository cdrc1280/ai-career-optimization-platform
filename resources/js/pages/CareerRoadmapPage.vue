<template>
  <PageLayout title="Career Roadmap" subtitle="Your personalized path to achieving your career goals">
    <template #actions>
      <button class="btn btn-primary" @click="showCreateModal = true">Generate New Roadmap</button>
    </template>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="!roadmaps.length" class="text-center py-16 card mt-6">
      <div class="text-6xl mb-4">🗺️</div>
      <h3 class="text-xl font-bold text-white mb-2">No Roadmap Yet</h3>
      <p class="text-slate-400 mb-6 max-w-md mx-auto">Generate an AI-powered step-by-step roadmap to transition from your current role to your dream job.</p>
      <button class="btn btn-primary" @click="showCreateModal = true">Create My Roadmap</button>
    </div>
    
    <div v-else class="space-y-8 mt-6">
      <div v-for="roadmap in roadmaps" :key="roadmap.id" class="card relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl -mt-10 -mr-10 pointer-events-none"></div>
        
        <div class="flex items-center justify-between mb-8">
          <div>
            <div class="flex items-center gap-3 text-sm text-slate-400 font-medium mb-1">
              <span class="text-slate-300">{{ roadmap.current_title }}</span>
              <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              <span class="text-blue-400">{{ roadmap.goal_title }}</span>
            </div>
            <h2 class="text-2xl font-bold text-white">Your Transition Plan</h2>
          </div>
          <button @click="deleteRoadmap(roadmap.id)" class="text-slate-400 hover:text-red-400 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>

        <!-- Timeline -->
        <div class="relative pl-4 md:pl-8 border-l-2 border-slate-700/50 space-y-8">
          <div v-for="month in roadmap.roadmap_data.months" :key="month.month" class="relative">
            <!-- Node -->
            <div class="absolute -left-[21px] md:-left-[37px] top-1 w-10 h-10 rounded-full bg-slate-900 border-4 border-slate-800 flex items-center justify-center shadow-lg">
              <span class="text-xs font-bold text-blue-500">M{{ month.month }}</span>
            </div>
            
            <div class="glass p-5 rounded-xl border border-white/05 hover:border-blue-500/30 transition-colors ml-4 shadow-sm">
              <h3 class="text-lg font-bold text-white mb-2">{{ month.focus }}</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                  <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Skills to Acquire</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="skill in month.skills" :key="skill" class="badge badge-blue">
                      {{ skill }}
                    </span>
                  </div>
                </div>
                
                <div v-if="month.resources && month.resources.length">
                  <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Suggested Resources</h4>
                  <ul class="space-y-1.5">
                    <li v-for="res in month.resources" :key="res" class="text-sm text-slate-300 flex items-start gap-2">
                      <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                      {{ res }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <Modal v-if="showCreateModal" :show="showCreateModal" @close="showCreateModal = false" title="Generate Career Roadmap">
      <form @submit.prevent="generateRoadmap" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Current Job Title</label>
          <input v-model="form.current_title" type="text" class="input" placeholder="e.g. Junior Frontend Developer" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Goal Job Title</label>
          <input v-model="form.goal_title" type="text" class="input" placeholder="e.g. Senior Fullstack Engineer" required>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="generating">
            <span v-if="generating" class="animate-spin mr-2">⏳</span>
            {{ generating ? 'Generating AI Roadmap...' : 'Generate Roadmap' }}
          </button>
        </div>
      </form>
    </Modal>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import Modal from '../components/ui/Modal.vue'
import api from '../services/api'

const roadmaps = ref<any[]>([])
const loading = ref(true)
const showCreateModal = ref(false)
const generating = ref(false)
const form = ref({ current_title: '', goal_title: '' })

onMounted(async () => {
  await fetchRoadmaps()
})

async function fetchRoadmaps() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/roadmaps')
    roadmaps.value = res.data
  } catch (e) {
    console.error('Failed to fetch roadmaps', e)
  } finally {
    loading.value = false
  }
}

async function generateRoadmap() {
  generating.value = true
  try {
    const res = await api.post('/api/v1/roadmaps', form.value)
    roadmaps.value.unshift(res.data)
    showCreateModal.value = false
    form.value = { current_title: '', goal_title: '' }
  } catch (e) {
    alert('Failed to generate roadmap')
  } finally {
    generating.value = false
  }
}

async function deleteRoadmap(id: number) {
  if (!confirm('Are you sure you want to delete this roadmap?')) return
  try {
    await api.delete(`/api/v1/roadmaps/${id}`)
    roadmaps.value = roadmaps.value.filter(r => r.id !== id)
  } catch (e) {
    alert('Failed to delete roadmap')
  }
}
</script>
