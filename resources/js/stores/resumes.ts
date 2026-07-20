import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as api from '../services/api'

export const useResumesStore = defineStore('resumes', () => {
    const resumes = ref<any[]>([])
    const current = ref<any>(null)
    const loading = ref(false)
    const uploading = ref(false)

    async function fetch() {
        loading.value = true
        try {
            const res = await api.getResumes()
            resumes.value = res.data?.data ?? res.data ?? []
        } finally { loading.value = false }
    }

    async function fetchOne(id: number) {
        loading.value = true
        try {
            const res = await api.getResume(id)
            current.value = res.data
        } finally { loading.value = false }
    }

    async function upload(file: File) {
        uploading.value = true
        try {
            const fd = new FormData()
            fd.append('file', file)
            const res = await api.uploadResume(fd)
            resumes.value.unshift(res.data)
            return res.data
        } finally { uploading.value = false }
    }

    async function remove(id: number) {
        await api.deleteResume(id)
        resumes.value = resumes.value.filter(r => r.id !== id)
    }

    return { resumes, current, loading, uploading, fetch, fetchOne, upload, remove }
})
