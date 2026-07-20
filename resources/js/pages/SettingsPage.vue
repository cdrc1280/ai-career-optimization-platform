<template>
  <div class="space-y-6">
    <div class="page-header">
      <h1 class="page-title">Settings</h1>
      <p class="page-subtitle">Manage your account and preferences</p>
    </div>

    <div class="tab-bar">
      <button v-for="tab in tabs" :key="tab.id" 
              class="tab-item" :class="{ 'active': activeTab === tab.id }"
              @click="activeTab = tab.id">
        {{ tab.label }}
      </button>
    </div>

    <div v-if="activeTab === 'account'" class="card p-6 space-y-6">
      <h3 class="text-xl font-semibold text-text-primary border-b border-slate-700/50 pb-4">Account Profile</h3>
      
      <div class="flex items-center gap-6">
        <div class="relative w-20 h-20 rounded-full overflow-hidden bg-slate-800 flex items-center justify-center border border-slate-700 group cursor-pointer">
          <img v-if="store.account?.profile?.avatar_url" :src="store.account.profile.avatar_url" class="w-full h-full object-cover" />
          <span v-else class="text-2xl font-bold text-slate-400">{{ store.account?.name?.charAt(0) || 'U' }}</span>
          <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
            <span class="text-xs text-white">Upload</span>
          </div>
          <input type="file" class="hidden absolute inset-0 w-full h-full cursor-pointer z-10" accept="image/*" @change="handleAvatarUpload" />
        </div>
        <div>
          <h4 class="font-medium text-text-primary">Profile Picture</h4>
          <p class="text-xs text-text-muted mt-1">JPG, GIF or PNG. Max size of 2MB.</p>
        </div>
      </div>

      <div class="space-y-4 max-w-xl">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input v-model="accountForm.name" type="text" class="input" />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="accountForm.email" type="email" class="input" />
          <p class="text-xs text-yellow-500/80 mt-1">Note: changing email requires re-verification</p>
        </div>
      </div>
      
      <div class="pt-4 border-t border-slate-700/50">
        <button @click="saveAccount" :disabled="store.saving" class="btn btn-primary">
          <span v-if="store.saving" class="spinner mr-2"></span>
          Save Changes
        </button>
      </div>
    </div>

    <div v-if="activeTab === 'security'" class="card p-6 space-y-6">
      <h3 class="text-xl font-semibold text-text-primary border-b border-slate-700/50 pb-4">Security Settings</h3>
      
      <div class="max-w-xl space-y-4">
        <h4 class="font-medium text-text-primary">Change Password</h4>
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input v-model="passwordForm.current" type="password" class="input" />
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input v-model="passwordForm.password" type="password" class="input" />
          <div class="mt-2 h-1.5 w-full bg-slate-800 rounded-full overflow-hidden flex">
             <div class="h-full" :class="passwordStrengthColor" :style="{width: passwordStrength + '%'}"></div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input v-model="passwordForm.password_confirmation" type="password" class="input" />
        </div>
        <button @click="changePassword" :disabled="store.saving || !passwordForm.password" class="btn btn-primary mt-2">Update Password</button>
      </div>
      
      <div class="pt-6 border-t border-slate-700/50">
        <div class="flex items-center justify-between max-w-xl">
          <div>
            <h4 class="font-medium text-text-primary">Two-Factor Authentication</h4>
            <p class="text-sm text-text-muted mt-1">Add an extra layer of security to your account.</p>
          </div>
          <span class="badge badge-blue">Coming Soon</span>
        </div>
      </div>
      
      <div class="pt-6 border-t border-slate-700/50">
        <h4 class="font-medium text-text-primary mb-4">Active Sessions</h4>
        <div class="glass p-4 rounded-lg flex items-start gap-4 max-w-xl">
           <svg class="w-6 h-6 text-green-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
           <div>
             <div class="text-sm font-medium text-text-primary">Current Session (Windows, Chrome)</div>
             <div class="text-xs text-text-muted mt-1">IP: 192.168.1.1</div>
             <div class="text-[10px] text-green-400 mt-1">Active Now</div>
           </div>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'preferences'" class="card p-6 space-y-6">
      <h3 class="text-xl font-semibold text-text-primary border-b border-slate-700/50 pb-4">Preferences</h3>
      
      <div class="max-w-xl space-y-6">
        <div>
          <h4 class="font-medium text-text-primary mb-3">Email Notifications</h4>
          <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-900" checked>
              <span class="text-sm text-text-primary">Analysis complete</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-900" checked>
              <span class="text-sm text-text-primary">Optimization complete</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-900" checked>
              <span class="text-sm text-text-primary">Cover letter ready</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-900" checked>
              <span class="text-sm text-text-primary">Application reminders</span>
            </label>
          </div>
        </div>
        
        <div class="pt-4 border-t border-slate-700/50">
          <h4 class="font-medium text-text-primary mb-3">Theme</h4>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border border-blue-500/50 bg-blue-500/10">
              <input type="radio" name="theme" value="dark" checked class="text-blue-500 bg-slate-800 border-slate-600">
              <span class="text-sm text-text-primary">Dark Mode</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border border-slate-700 bg-slate-800/30 opacity-60">
              <input type="radio" name="theme" value="light" disabled class="text-blue-500 bg-slate-800 border-slate-600">
              <span class="text-sm text-text-primary flex items-center gap-2">Light Mode <span class="badge badge-blue text-[10px] py-0">Soon</span></span>
            </label>
          </div>
        </div>
        
        <div class="pt-4 border-t border-slate-700/50">
          <h4 class="font-medium text-text-primary mb-3">Default Export Format</h4>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="export" value="pdf" checked class="text-blue-500 bg-slate-800 border-slate-600">
              <span class="text-sm text-text-primary">PDF (.pdf)</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="export" value="docx" class="text-blue-500 bg-slate-800 border-slate-600">
              <span class="text-sm text-text-primary">Word (.docx)</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'danger'" class="card border-red-500/30 p-6 space-y-6">
      <h3 class="text-xl font-semibold text-red-400 border-b border-red-500/20 pb-4">Danger Zone</h3>
      
      <div class="max-w-xl">
        <h4 class="font-medium text-text-primary">Delete Account</h4>
        <p class="text-sm text-text-muted mt-2 mb-4">
          Once you delete your account, there is no going back. Please be certain. All your data including resumes, cover letters, and applications will be permanently removed.
        </p>
        
        <div class="bg-red-500/10 border border-red-500/20 p-4 rounded-lg space-y-4">
          <div class="form-group mb-0">
            <label class="form-label text-red-200">Confirm Password to Delete</label>
            <input v-model="deleteForm.password" type="password" class="input border-red-500/30 focus:border-red-500" />
          </div>
          <button @click="deleteAccount" :disabled="!deleteForm.password" class="btn btn-danger w-full justify-center">Delete My Account</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useAccountStore } from '../stores/account'
import { addToast } from '../composables/toast'

const store = useAccountStore()
const activeTab = ref('account')
const tabs = [
  { id: 'account', label: 'Account' },
  { id: 'security', label: 'Security' },
  { id: 'preferences', label: 'Preferences' },
  { id: 'danger', label: 'Danger Zone' },
]

const accountForm = ref({ name: '', email: '' })
const passwordForm = ref({ current: '', password: '', password_confirmation: '' })
const deleteForm = ref({ password: '' })

watch(() => store.account, (val) => {
  if (val) {
    accountForm.value.name = val.name || ''
    accountForm.value.email = val.email || ''
  }
}, { immediate: true })

onMounted(() => {
  if (!store.account) store.fetch()
})

const passwordStrength = computed(() => {
  const p = passwordForm.value.password
  if (!p) return 0
  let score = 0
  if (p.length > 7) score += 25
  if (p.match(/[A-Z]/)) score += 25
  if (p.match(/[0-9]/)) score += 25
  if (p.match(/[^A-Za-z0-9]/)) score += 25
  return score
})

const passwordStrengthColor = computed(() => {
  if (passwordStrength.value < 50) return 'bg-red-500'
  if (passwordStrength.value < 100) return 'bg-yellow-500'
  return 'bg-green-500'
})

async function saveAccount() {
  await store.update(accountForm.value)
}

async function changePassword() {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    addToast('error', 'Passwords do not match')
    return
  }
  await store.updatePassword(passwordForm.value)
  passwordForm.value = { current: '', password: '', password_confirmation: '' }
}

function handleAvatarUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) store.uploadAvatarFile(file)
}

function deleteAccount() {
  if (confirm('Are you ABSOLUTELY sure? This cannot be undone.')) {
    addToast('error', 'Account deletion simulated')
    // store.deleteAccount(...)
  }
}
</script>
