<template>
  <div class="space-y-6">
    <div class="page-header flex justify-between items-end">
      <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Stay updated on your career optimization progress</p>
      </div>
      <button v-if="unreadCount > 0" @click="markAllRead" class="btn btn-secondary">
        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Mark all as read
      </button>
    </div>

    <div class="tab-bar">
      <button v-for="tab in tabs" :key="tab.id" 
              class="tab-item" :class="{ 'active': activeFilter === tab.id }"
              @click="activeFilter = tab.id">
        {{ tab.label }}
      </button>
    </div>

    <div class="card overflow-hidden">
      <div v-if="loading && items.length === 0" class="p-12 flex justify-center">
        <div class="spinner w-8 h-8"></div>
      </div>
      
      <div v-else-if="filteredItems.length === 0" class="empty-state p-12 text-center">
        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">📭</div>
        <h3 class="text-lg font-medium text-text-primary mb-1">No notifications</h3>
        <p class="text-text-muted">You're all caught up! Check back later.</p>
      </div>

      <div v-else class="divide-y divide-slate-700/50">
        <div v-for="n in filteredItems" :key="n.id" 
             class="p-4 sm:p-6 hover:bg-slate-800/30 transition-colors flex gap-4 cursor-pointer"
             :class="{'bg-blue-500/5': !n.read_at}"
             @click="handleNotificationClick(n)">
          <div class="flex-shrink-0 mt-1">
            <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xl shadow-inner">
              <span v-if="n.type === 'analysis'">🎯</span>
              <span v-else-if="n.type === 'optimization'">✨</span>
              <span v-else-if="n.type === 'cover_letter'">✉️</span>
              <span v-else>🔔</span>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
              <div class="flex items-center gap-2">
                <span v-if="!n.read_at" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 shadow-[0_0_5px_rgba(59,130,246,0.5)]"></span>
                <h4 class="text-sm font-semibold text-text-primary truncate" :class="{'text-white': !n.read_at}">{{ n.title }}</h4>
              </div>
              <span class="text-xs text-slate-500 whitespace-nowrap">{{ n.created_at }}</span>
            </div>
            <p class="text-sm text-text-muted line-clamp-2" :class="{'text-slate-300': !n.read_at}">{{ n.message }}</p>
          </div>
        </div>
      </div>
      
      <div v-if="hasMore" class="p-4 border-t border-slate-700/50 text-center bg-slate-800/20">
        <button @click="loadMore" class="text-sm text-blue-400 hover:text-blue-300 font-medium disabled:opacity-50" :disabled="loading">
          <span v-if="loading">Loading...</span>
          <span v-else>Load more</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getNotifications, markNotificationRead, markAllNotificationsRead } from '../services/api'
import { useNotificationsStore } from '../stores/notifications'

const router = useRouter()
const store = useNotificationsStore()

const items = ref<any[]>([])
const loading = ref(false)
const hasMore = ref(false)
const page = ref(1)

const activeFilter = ref('all')
const tabs = [
  { id: 'all', label: 'All' },
  { id: 'unread', label: 'Unread' },
  { id: 'analysis', label: 'Analysis' },
  { id: 'optimization', label: 'Optimization' },
  { id: 'cover_letter', label: 'Cover Letters' },
]

const unreadCount = computed(() => items.value.filter(n => !n.read_at).length)

const filteredItems = computed(() => {
  let list = items.value
  if (activeFilter.value === 'unread') return list.filter(n => !n.read_at)
  if (activeFilter.value !== 'all') return list.filter(n => n.type === activeFilter.value)
  return list
})

async function fetchNotifications(p = 1) {
  loading.value = true
  try {
    const res = await getNotifications()
    // Simulate pagination for this mock
    const newItems = res.data?.data ?? res.data ?? []
    if (p === 1) items.value = newItems
    else items.value = [...items.value, ...newItems]
    hasMore.value = res.data?.current_page < res.data?.last_page
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  page.value++
  await fetchNotifications(page.value)
}

async function markAllRead() {
  try {
    await markAllNotificationsRead()
    items.value.forEach(n => n.read_at = new Date().toISOString())
    store.fetchUnread()
  } catch (e) {
    console.error(e)
  }
}

async function handleNotificationClick(n: any) {
  if (!n.read_at) {
    n.read_at = new Date().toISOString()
    try {
      await markNotificationRead(n.id)
      store.fetchUnread()
    } catch (e) {}
  }
  
  if (n.type === 'analysis' && n.job_id) {
    router.push(`/applications/${n.job_id}`)
  } else if (n.type === 'optimization' && n.resume_id) {
    router.push(`/resumes/${n.resume_id}`)
  } else if (n.type === 'cover_letter' && n.cover_letter_id) {
    router.push('/cover-letters')
  }
}

onMounted(() => {
  fetchNotifications()
})
</script>
