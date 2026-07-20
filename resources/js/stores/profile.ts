import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as api from '../services/api'

export const useProfileStore = defineStore('profile', () => {
    const profile = ref<any>(null)
    const loading = ref(false)

    async function fetch() {
        loading.value = true
        try {
            const res = await api.getProfile()
            profile.value = res.data
        } finally {
            loading.value = false
        }
    }

    async function update(data: any) {
        const res = await api.updateProfile(data)
        profile.value = res.data
        return res.data
    }

    return { profile, loading, fetch, update }
})
