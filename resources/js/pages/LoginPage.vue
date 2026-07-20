<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router  = useRouter()
const auth    = useAuthStore()
const loading = ref(false)
const error   = ref('')
const showPw  = ref(false)

const form = reactive({ email: '', password: '', remember: false })

async function submit() {
    if (!form.email || !form.password) return
    error.value   = ''
    loading.value = true
    try {
        await auth.login(form.email, form.password)
        router.push('/dashboard')
    } catch (e: any) {
        const msg = e?.response?.data?.message ?? ''
        error.value = msg || 'Invalid email or password. Please try again.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
  <div class="auth-page">
    <!-- Ambient background orbs -->
    <div class="auth-bg-orb" style="width:360px;height:360px;top:-80px;left:-100px;background:radial-gradient(circle,rgba(59,130,246,0.15),transparent 70%);animation-delay:0s" />
    <div class="auth-bg-orb" style="width:280px;height:280px;bottom:-60px;right:-60px;background:radial-gradient(circle,rgba(99,102,241,0.12),transparent 70%);animation-delay:3s" />

    <div class="auth-card">
      <!-- Branding -->
      <div class="text-center mb-8">
        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 6px 24px rgba(59,130,246,0.4)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#e2e8f0;line-height:1.2">Welcome back</h1>
        <p style="color:#94a3b8;font-size:0.875rem;margin-top:6px">Sign in to your CareerAI account</p>
      </div>

      <!-- Error alert -->
      <Transition name="shake">
        <div v-if="error" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
          <span style="color:#fca5a5;flex-shrink:0;font-size:0.9rem">⚠</span>
          <p style="color:#fca5a5;font-size:0.875rem">{{ error }}</p>
        </div>
      </Transition>

      <!-- Social login -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:20px">
        <a href="/auth/redirect/google" class="social-btn">
          <svg width="16" height="16" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          <span>Google</span>
        </a>
        <a href="/auth/redirect/github" class="social-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2A10 10 0 002 12c0 4.42 2.87 8.17 6.84 9.5.5.08.66-.23.66-.5v-1.69c-2.77.6-3.36-1.34-3.36-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.87 1.52 2.34 1.07 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.92 0-1.11.38-2 1.03-2.71-.1-.25-.45-1.29.1-2.64 0 0 .84-.27 2.75 1.02.79-.22 1.65-.33 2.5-.33.85 0 1.71.11 2.5.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.35.2 2.39.1 2.64.65.71 1.03 1.6 1.03 2.71 0 3.82-2.34 4.66-4.57 4.91.36.31.69.92.69 1.85V21c0 .27.16.59.67.5C19.14 20.16 22 16.42 22 12A10 10 0 0012 2z"/></svg>
          <span>GitHub</span>
        </a>
        <a href="/auth/redirect/microsoft" class="social-btn">
          <svg width="16" height="16" viewBox="0 0 23 23"><path fill="#f3f3f3" d="M0 0h23v23H0z"/><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/></svg>
          <span>Microsoft</span>
        </a>
      </div>

      <div class="divider" style="margin-bottom:20px">or sign in with email</div>

      <!-- Form -->
      <form @submit.prevent="submit" novalidate>
        <div style="display:flex;flex-direction:column;gap:14px">
          <!-- Email -->
          <div class="form-group">
            <label class="form-label" for="login-email">Email address</label>
            <div class="input-group">
              <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
              <input
                id="login-email"
                v-model="form.email"
                type="email"
                class="input"
                placeholder="you@example.com"
                autocomplete="email"
                required
                :disabled="loading"
              />
            </div>
          </div>

          <!-- Password -->
          <div class="form-group">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
              <label class="form-label" for="login-password" style="margin:0">Password</label>
              <a href="/forgot-password" class="link" style="font-size:0.8rem">Forgot password?</a>
            </div>
            <div class="input-group">
              <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
              <input
                id="login-password"
                v-model="form.password"
                :type="showPw ? 'text' : 'password'"
                class="input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
                :disabled="loading"
              />
              <button type="button" class="input-suffix" @click="showPw = !showPw" :aria-label="showPw ? 'Hide password' : 'Show password'">
                <svg v-if="!showPw" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <!-- Remember -->
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.875rem;color:#94a3b8">
            <input v-model="form.remember" type="checkbox" style="width:15px;height:15px" />
            Keep me signed in
          </label>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary btn-lg" :disabled="loading" style="width:100%;margin-top:4px">
            <div v-if="loading" class="spinner" style="width:16px;height:16px" />
            <span>{{ loading ? 'Signing in…' : 'Sign in' }}</span>
          </button>
        </div>
      </form>

      <p style="text-align:center;font-size:0.875rem;color:#64748b;margin-top:22px">
        Don't have an account?
        <RouterLink to="/register" class="link" style="font-weight:600">Create account</RouterLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.shake-enter-active { animation: shake 0.35s ease both; }

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%       { transform: translateX(-6px); }
  40%       { transform: translateX(6px); }
  60%       { transform: translateX(-4px); }
  80%       { transform: translateX(4px); }
}
</style>
