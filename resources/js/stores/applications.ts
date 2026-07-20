import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as api from '../services/api'

export const useApplicationsStore = defineStore('applications', () => {
    const applications = ref<any[]>([])
    const loading = ref(false)

    async function fetch() {
        loading.value = true
        try {
            const res = await api.getApplications()
            applications.value = res.data?.data ?? res.data ?? []
        } finally { loading.value = false }
    }

    async function create(data: any) {
        const res = await api.createApplication(data)
        applications.value.unshift(res.data)
        return res.data
    }

    async function updateStatus(id: number, status: string, extra?: any) {
        const res = await api.updateApplication(id, { status, ...extra })
        const idx = applications.value.findIndex(a => a.id === id)
        if (idx !== -1) applications.value[idx] = res.data
        return res.data
    }

    async function update(id: number, data: any) {
        const res = await api.updateApplication(id, data)
        const idx = applications.value.findIndex(a => a.id === id)
        if (idx !== -1) applications.value[idx] = res.data
        return res.data
    }

    async function remove(id: number) {
        await api.deleteApplication(id)
        applications.value = applications.value.filter(a => a.id !== id)
    }

    return { applications, loading, fetch, create, updateStatus, update, remove }
})
