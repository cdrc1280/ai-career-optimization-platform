import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getCopilotSessions, getCopilotSession, sendCopilotMessage } from '../services/api'

export const useCopilotStore = defineStore('copilot', () => {
    const sessions = ref<any[]>([])
    const activeSession = ref<any>(null)
    const activeMessages = ref<any[]>([])
    const isDrawerOpen = ref(false)
    const isLoading = ref(false)
    const isTyping = ref(false)

    async function fetchSessions() {
        try {
            const res = await getCopilotSessions()
            sessions.value = res.data
        } catch (e) {
            console.error('Failed to load copilot sessions', e)
        }
    }

    async function loadSession(id: number) {
        isLoading.value = true
        try {
            const res = await getCopilotSession(id)
            activeSession.value = res.data
            activeMessages.value = res.data.messages || []
        } catch (e) {
            console.error('Failed to load session', e)
        } finally {
            isLoading.value = false
        }
    }

    async function sendMessage(content: string) {
        // Optimistically add user message
        const tempId = Date.now()
        activeMessages.value.push({ id: tempId, role: 'user', content, created_at: new Date().toISOString() })
        isTyping.value = true

        try {
            const res = await sendCopilotMessage({
                session_id: activeSession.value?.id,
                message: content
            })
            
            // If it was a new session, update activeSession
            if (!activeSession.value?.id && res.data.session) {
                activeSession.value = res.data.session
                await fetchSessions()
            }
            
            // Replace the local messages with the server state
            if (res.data.messages) {
                activeMessages.value = res.data.messages
            } else if (res.data.reply) {
                activeMessages.value.push(res.data.reply)
            }
        } catch (e) {
            console.error('Failed to send message', e)
            // Remove optimistic message on fail
            activeMessages.value = activeMessages.value.filter(m => m.id !== tempId)
        } finally {
            isTyping.value = false
        }
    }

    function toggleDrawer() {
        isDrawerOpen.value = !isDrawerOpen.value
    }

    function startNewSession() {
        activeSession.value = null
        activeMessages.value = []
    }

    return {
        sessions,
        activeSession,
        activeMessages,
        isDrawerOpen,
        isLoading,
        isTyping,
        fetchSessions,
        loadSession,
        sendMessage,
        toggleDrawer,
        startNewSession
    }
})
