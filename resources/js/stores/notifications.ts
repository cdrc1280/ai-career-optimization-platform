import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getUnreadNotifications, markNotificationRead, markAllNotificationsRead } from '../services/api'

export const useNotificationsStore = defineStore('notifications', () => {
    const items = ref<any[]>([])
    const loading = ref(false)
    const unreadCount = computed(() => items.value.filter(n => !n.read_at).length)

    async function fetchUnread() {
        loading.value = true
        try {
            const res = await getUnreadNotifications()
            items.value = res.data?.data ?? res.data ?? []
        } catch { /* silently ignore */ }
        finally { loading.value = false }
    }

    async function markRead(id: string) {
        await markNotificationRead(id)
        const n = items.value.find(x => x.id === id)
        if (n) n.read_at = new Date().toISOString()
    }

    async function markAllRead() {
        await markAllNotificationsRead()
        items.value.forEach(n => n.read_at = new Date().toISOString())
    }

    return { items, loading, unreadCount, fetchUnread, markRead, markAllRead }
})
