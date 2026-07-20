import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as api from '../services/api'

export const useJobPostingsStore = defineStore('jobPostings', () => {
    const jobPostings = ref<any[]>([])
    const loading = ref(false)
    const creating = ref(false)

    async function fetch() {
        loading.value = true
        try {
            const res = await api.getJobPostings()
            jobPostings.value = res.data?.data ?? res.data ?? []
        } finally { loading.value = false }
    }

    async function create(data: any) {
        creating.value = true
        try {
            const res = await api.createJobPosting(data)
            jobPostings.value.unshift(res.data)
            return res.data
        } finally { creating.value = false }
    }

    async function remove(id: number) {
        await api.deleteJobPosting(id)
        jobPostings.value = jobPostings.value.filter(j => j.id !== id)
    }

    return { jobPostings, loading, creating, fetch, create, remove }
})
