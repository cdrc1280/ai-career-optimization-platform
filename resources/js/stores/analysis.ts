import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as api from '../services/api'

export const useAnalysisStore = defineStore('analysis', () => {
    const analyses = ref<any[]>([])
    const current = ref<any>(null)
    const loading = ref(false)
    const analyzing = ref(false)

    async function fetchAll() {
        loading.value = true
        try {
            const res = await api.getAnalyses()
            analyses.value = res.data?.data ?? res.data ?? []
        } finally { loading.value = false }
    }

    async function fetchOne(versionId: number, jobId: number) {
        loading.value = true
        try {
            const res = await api.getAnalysis(versionId, jobId)
            current.value = res.data
            return res.data
        } finally { loading.value = false }
    }

    async function analyze(versionId: number, jobId: number) {
        analyzing.value = true
        try {
            await api.triggerAnalysis(versionId, jobId)
            // Poll until done
            let attempts = 0
            while (attempts < 30) {
                await new Promise(r => setTimeout(r, 2000))
                try {
                    const res = await api.getAnalysis(versionId, jobId)
                    if (res.data?.status === 'completed') {
                        current.value = res.data
                        return res.data
                    }
                    if (res.data?.status === 'failed') throw new Error('Analysis failed')
                } catch (e: any) {
                    if (e?.response?.status !== 404) throw e
                }
                attempts++
            }
            throw new Error('Analysis timed out')
        } finally { analyzing.value = false }
    }

    return { analyses, current, loading, analyzing, fetchAll, fetchOne, analyze }
})
