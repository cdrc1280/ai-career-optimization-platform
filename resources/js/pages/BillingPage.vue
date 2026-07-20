<template>
  <div class="space-y-8">
    <div class="page-header">
      <h1 class="page-title">Billing & Plans</h1>
      <p class="page-subtitle">Manage your subscription and billing details</p>
    </div>

    <!-- Current Plan -->
    <div class="card p-6 bg-gradient-to-br from-slate-900 to-slate-800 border-blue-500/30">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <h2 class="text-2xl font-bold text-white">Free Plan</h2>
            <span class="badge badge-blue text-xs uppercase tracking-wider px-2 py-1">Current</span>
          </div>
          <p class="text-slate-400 text-sm">Basic features for getting started. No payment required.</p>
        </div>
        
        <div class="flex items-center gap-3 bg-slate-900/50 p-1.5 rounded-xl border border-slate-700/50">
          <button class="px-4 py-2 rounded-lg text-sm font-medium" :class="billingCycle === 'monthly' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'" @click="billingCycle = 'monthly'">Monthly</button>
          <button class="px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2" :class="billingCycle === 'annual' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-white'" @click="billingCycle = 'annual'">
            Annual <span class="text-[10px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded uppercase">-20%</span>
          </button>
        </div>
      </div>
      
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-300">Resume Optimizations</span>
            <span class="text-blue-400 font-medium">1 / 3</span>
          </div>
          <div class="progress-track"><div class="progress-fill w-1/3 bg-blue-500"></div></div>
        </div>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-300">Cover Letters</span>
            <span class="text-indigo-400 font-medium">2 / 3</span>
          </div>
          <div class="progress-track"><div class="progress-fill w-2/3 bg-indigo-500"></div></div>
        </div>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-300">Job Analyses</span>
            <span class="text-green-400 font-medium">5 / 5</span>
          </div>
          <div class="progress-track"><div class="progress-fill w-full bg-green-500"></div></div>
        </div>
      </div>
    </div>

    <!-- Pricing Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="card p-6 flex flex-col border-blue-500/50 shadow-[0_0_15px_rgba(59,130,246,0.1)] relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-bl-full -mr-16 -mt-16"></div>
        <h3 class="text-xl font-bold text-white mb-2">Free</h3>
        <div class="flex items-baseline gap-1 mb-6">
          <span class="text-4xl font-extrabold text-white">$0</span>
          <span class="text-slate-400">/mo</span>
        </div>
        <ul class="space-y-3 mb-8 flex-1">
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 3 Resume optimizations</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 3 Cover letters</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 5 Job analyses</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Basic export (PDF)</li>
        </ul>
        <button class="btn bg-slate-800 text-slate-400 cursor-default w-full justify-center" disabled>Current Plan</button>
      </div>

      <div class="card p-6 flex flex-col border-indigo-500 relative overflow-hidden transform md:-translate-y-2 shadow-[0_10px_30px_rgba(99,102,241,0.2)]">
        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
        <div class="absolute top-4 right-4"><span class="badge bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Popular</span></div>
        <h3 class="text-xl font-bold text-white mb-2">Premium</h3>
        <div class="flex items-baseline gap-1 mb-6">
          <span class="text-4xl font-extrabold text-white">${{ billingCycle === 'monthly' ? '19' : '15' }}</span>
          <span class="text-slate-400">/mo</span>
        </div>
        <ul class="space-y-3 mb-8 flex-1">
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Unlimited</strong> everything</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Interview prep tools</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Career recommendations</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Priority AI processing</li>
        </ul>
        <button class="btn btn-primary w-full justify-center bg-gradient-to-r from-blue-600 to-indigo-600 border-0" @click="upgrade('Premium', billingCycle === 'monthly' ? 19 : 15, 2)">Upgrade to Premium</button>
      </div>

      <div class="card p-6 flex flex-col relative overflow-hidden">
        <h3 class="text-xl font-bold text-white mb-2">Enterprise</h3>
        <div class="flex items-baseline gap-1 mb-6">
          <span class="text-4xl font-extrabold text-white">${{ billingCycle === 'monthly' ? '49' : '39' }}</span>
          <span class="text-slate-400">/mo</span>
        </div>
        <ul class="space-y-3 mb-8 flex-1">
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Everything in Premium</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Team features <span class="badge badge-yellow text-[10px] py-0 px-1 ml-1">Soon</span></li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Custom branding</li>
          <li class="flex items-start gap-3 text-sm text-slate-300"><svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Dedicated support</li>
        </ul>
        <button class="btn border border-slate-600 hover:bg-slate-800 text-white w-full justify-center" @click="upgrade('Enterprise', billingCycle === 'monthly' ? 49 : 39, 3)">Contact Sales</button>
      </div>
    </div>

    <!-- Payment Method -->
    <div class="card p-6">
      <h3 class="text-lg font-bold text-white mb-4">Payment Method</h3>
      <div class="glass p-6 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center text-slate-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
          </div>
          <div>
            <p class="font-medium text-white">No payment method added</p>
            <p class="text-sm text-slate-400 mt-1">Add a card to upgrade your plan</p>
          </div>
        </div>
        <button class="btn btn-secondary" @click="upgrade('Premium', 19, 2)">Add Payment Method</button>
      </div>
    </div>
    
    <CheckoutModal 
      :show="checkoutModal.show"
      :plan="checkoutModal.plan"
      @close="checkoutModal.show = false"
      @success="handleCheckoutSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { addToast } from '../composables/toast'
import CheckoutModal from '../components/CheckoutModal.vue'

const billingCycle = ref('monthly')
const checkoutModal = ref({ show: false, plan: null as any })

function upgrade(name: string, price: number, id: number) {
  checkoutModal.value = {
    show: true,
    plan: {
      id,
      name,
      price_monthly: price,
      limits: {
        resumes_per_month: name === 'Premium' ? 'Unlimited' : 3,
        analyses_per_month: name === 'Premium' ? 'Unlimited' : 5
      }
    }
  }
}

function handleCheckoutSuccess() {
  addToast('success', 'Plan upgraded successfully!')
  // In a real app, we would refresh the auth store or user profile to get new limits
}
</script>
