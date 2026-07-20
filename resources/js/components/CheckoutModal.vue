<template>
  <Modal :show="show" @close="$emit('close')">
    <template #header>
      Upgrade to {{ plan?.name }}
    </template>
    
    <template #body>
      <div v-if="!success" class="space-y-6">
        <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-xl">
          <div class="flex justify-between items-center mb-2">
            <span class="font-medium text-white">{{ plan?.name }} Plan</span>
            <span class="text-xl font-bold text-white">${{ plan?.price_monthly }}<span class="text-sm text-slate-400">/mo</span></span>
          </div>
          <ul class="text-sm text-slate-300 space-y-2 mt-4">
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              {{ plan?.limits?.resumes_per_month }} Resumes / month
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              {{ plan?.limits?.analyses_per_month }} AI Analyses / month
            </li>
          </ul>
        </div>
        
        <div class="form-group">
          <label class="form-label">Name on Card</label>
          <input type="text" class="input" placeholder="Jane Doe" v-model="form.name" />
        </div>
        
        <div class="form-group">
          <label class="form-label">Card Details (Simulated)</label>
          <div class="p-3 bg-slate-900 border border-slate-700 rounded-lg flex items-center justify-between">
            <div class="flex gap-2 items-center text-slate-300 text-sm">
              <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
              •••• •••• •••• 4242
            </div>
            <span class="text-xs text-slate-500">12/28</span>
          </div>
          <p class="text-xs text-slate-500 mt-2">This is a simulated checkout environment. No real charges will be made.</p>
        </div>
      </div>
      
      <div v-else class="text-center py-8 space-y-4">
        <div class="w-16 h-16 bg-green-500/20 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
        <h3 class="text-xl font-bold text-white">Upgrade Successful!</h3>
        <p class="text-slate-400">Your account has been upgraded to the {{ plan?.name }} plan. Your limits have been instantly refreshed.</p>
      </div>
    </template>
    
    <template #footer v-if="!success">
      <button class="btn btn-secondary" @click="$emit('close')" :disabled="loading">Cancel</button>
      <button class="btn btn-primary bg-gradient-to-r from-blue-500 to-indigo-600 border-0" @click="simulateCheckout" :disabled="loading || !form.name">
        <span v-if="loading" class="mr-2 animate-spin">⌛</span>
        Pay ${{ plan?.price_monthly }} & Upgrade
      </button>
    </template>
    
    <template #footer v-else>
      <button class="btn btn-primary w-full" @click="$emit('close')">Return to Dashboard</button>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Modal from './Modal.vue'
import * as api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { addToast } from '../composables/toast'

const props = defineProps({
  show: Boolean,
  plan: Object
})

const emit = defineEmits(['close', 'success'])
const store = useAuthStore()

const loading = ref(false)
const success = ref(false)
const form = ref({ name: '' })

async function simulateCheckout() {
  if (!props.plan?.id) return
  loading.value = true
  try {
    const res = await api.simulateCheckout(props.plan.id)
    success.value = true
    // optionally refresh user data
    emit('success')
  } catch (e: any) {
    addToast('error', e?.response?.data?.message || 'Checkout failed')
  } finally {
    loading.value = false
  }
}
</script>
