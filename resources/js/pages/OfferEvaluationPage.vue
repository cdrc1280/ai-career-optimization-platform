<template>
  <PageLayout title="Offer Evaluation" subtitle="Upload your job offer letters for AI analysis and salary intelligence">
    <template #actions>
      <button class="btn btn-primary" @click="showUploadModal = true">Evaluate New Offer</button>
    </template>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="!offers.length" class="text-center py-16 card mt-6">
      <div class="text-6xl mb-4">💼</div>
      <h3 class="text-xl font-bold text-white mb-2">No Offers Evaluated Yet</h3>
      <p class="text-slate-400 mb-6 max-w-md mx-auto">Upload an offer letter or paste the details to get an AI breakdown of salary competitiveness, benefits, and hidden red flags.</p>
      <button class="btn btn-primary" @click="showUploadModal = true">Evaluate Offer</button>
    </div>

    <div v-else class="grid grid-cols-1 gap-6 mt-6">
      <div v-for="offer in offers" :key="offer.id" class="card relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-start gap-6">
          <div class="flex-1">
            <h3 class="text-xl font-bold text-white">{{ offer.job_title }}</h3>
            <p class="text-blue-400 font-medium mb-4">{{ offer.company_name }}</p>
            
            <div class="prose prose-invert prose-sm max-w-none text-slate-300 mb-6">
              <p>{{ offer.evaluation_data.overall_recommendation }}</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div class="glass p-3 rounded-lg border border-white/05">
                <span class="text-xs text-slate-400 uppercase tracking-wider block mb-1">Salary Competitiveness</span>
                <div class="flex items-end gap-2">
                  <span class="text-2xl font-bold text-white">{{ offer.evaluation_data.salary_score }}/100</span>
                </div>
              </div>
              <div class="glass p-3 rounded-lg border border-white/05">
                <span class="text-xs text-slate-400 uppercase tracking-wider block mb-1">Benefits Package</span>
                <div class="flex items-end gap-2">
                  <span class="text-2xl font-bold text-white">{{ offer.evaluation_data.benefits_score }}/100</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="w-full md:w-80 space-y-4">
            <div v-if="offer.evaluation_data.red_flags?.length" class="glass p-4 rounded-xl border border-red-500/30 bg-red-500/5">
              <h4 class="text-sm font-semibold text-red-400 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Red Flags
              </h4>
              <ul class="text-xs text-slate-300 space-y-1.5 list-disc list-inside">
                <li v-for="flag in offer.evaluation_data.red_flags" :key="flag">{{ flag }}</li>
              </ul>
            </div>
            
            <div v-if="offer.evaluation_data.negotiation_tips?.length" class="glass p-4 rounded-xl border border-green-500/30 bg-green-500/5">
              <h4 class="text-sm font-semibold text-green-400 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Negotiation Tips
              </h4>
              <ul class="text-xs text-slate-300 space-y-1.5 list-disc list-inside">
                <li v-for="tip in offer.evaluation_data.negotiation_tips" :key="tip">{{ tip }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <Modal v-if="showUploadModal" :show="showUploadModal" @close="showUploadModal = false" title="Evaluate New Offer">
      <form @submit.prevent="evaluateOffer" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Job Title</label>
          <input v-model="form.job_title" type="text" class="input" placeholder="e.g. Software Engineer" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
          <input v-model="form.company_name" type="text" class="input" placeholder="e.g. Google" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Offer Details (Text)</label>
          <textarea v-model="form.offer_details" class="input h-32" placeholder="Paste the text of your offer letter, salary, and benefits here..." required></textarea>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" class="btn btn-secondary" @click="showUploadModal = false">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="evaluating">
            <span v-if="evaluating" class="animate-spin mr-2">⏳</span>
            {{ evaluating ? 'Evaluating AI...' : 'Evaluate Offer' }}
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

const offers = ref<any[]>([])
const loading = ref(true)
const showUploadModal = ref(false)
const evaluating = ref(false)
const form = ref({ job_title: '', company_name: '', offer_details: '' })

onMounted(async () => {
  await fetchOffers()
})

async function fetchOffers() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/offer-evaluations')
    offers.value = res.data
  } catch (e) {
    console.error('Failed to fetch offers', e)
  } finally {
    loading.value = false
  }
}

async function evaluateOffer() {
  evaluating.value = true
  try {
    const res = await api.post('/api/v1/offer-evaluations', form.value)
    offers.value.unshift(res.data)
    showUploadModal.value = false
    form.value = { job_title: '', company_name: '', offer_details: '' }
  } catch (e) {
    alert('Failed to evaluate offer')
  } finally {
    evaluating.value = false
  }
}
</script>
