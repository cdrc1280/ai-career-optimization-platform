import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { loginApi, logout as apiLogout, isLoggedIn, getProfile } from '../services/api'

export const useAuthStore = defineStore('auth', () => {
    const user    = ref<any>(null)
    const profile = ref<any>(null)
    const loading = ref(false)

    const authenticated = computed(() => isLoggedIn())
    const displayName   = computed(() => user.value?.name ?? profile.value?.full_name ?? 'User')
    const displayEmail  = computed(() => user.value?.email ?? '')
    const initials      = computed(() => displayName.value.slice(0, 1).toUpperCase())

    function setUser(u: any) {
        user.value = u
    }

    async function login(email: string, password: string) {
        loading.value = true
        try {
            const res = await loginApi({ email, password })
            user.value = res.data?.user ?? null
            // Non-blocking profile fetch — do not let it crash login
            fetchProfile().catch(() => {})
            return res
        } finally {
            loading.value = false
        }
    }

    async function fetchProfile() {
        try {
            const res = await getProfile()
            profile.value = res.data
        } catch { /* silently ignore */ }
    }

    async function logout() {
        await apiLogout()
        user.value    = null
        profile.value = null
    }

    return {
        user, profile, loading, authenticated,
        displayName, displayEmail, initials,
        setUser, login, fetchProfile, logout,
    }
})
