<template>
  <div class="relative" ref="dropdownRef">
    <button @click="toggle" class="btn btn-ghost btn-icon relative rounded-full p-2 text-slate-300 hover:text-white">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span v-if="store.unreadCount > 0" class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-slate-900"></span>
    </button>
    
    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-3 w-80 bg-[#111e36]/95 backdrop-blur-xl border border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden z-50 transform origin-top-right transition-all"
    >
      <div class="p-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/30">
        <h3 class="font-semibold text-white">Notifications</h3>
        <button v-if="store.unreadCount > 0" @click="store.markAllRead" class="text-xs text-blue-400 hover:text-blue-300">Mark all read</button>
      </div>
      
      <div class="max-h-[300px] overflow-y-auto">
        <div v-if="store.items.length === 0" class="p-6 text-center text-slate-400 text-sm">
          All caught up! No new notifications
        </div>
        <div v-else class="flex flex-col">
          <div v-for="n in store.items.slice(0, 10)" :key="n.id" 
               class="p-3 border-b border-slate-700/50 last:border-0 hover:bg-slate-800/50 transition-colors cursor-pointer flex gap-3"
               :class="{'opacity-60': n.read_at}"
               @click="store.markRead(n.id)">
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="!n.read_at ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-800 text-slate-400'">
              <span v-if="n.data?.type === 'analysis'" class="text-xl">🎯</span>
              <span v-else-if="n.data?.type === 'optimization'" class="text-xl">✨</span>
              <span v-else-if="n.data?.type === 'cover_letter'" class="text-xl">✉️</span>
              <span v-else class="text-xl">🔔</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-text-primary flex justify-between">
                <span class="truncate">{{ n.data?.title || 'Notification' }}</span>
                <span v-if="!n.read_at" class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></span>
              </div>
              <div class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ n.data?.message || 'You have a new notification.' }}</div>
              <div class="text-[10px] text-slate-500 mt-1">{{ new Date(n.created_at).toLocaleString() || 'Just now' }}</div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="p-2 border-t border-slate-700/50 bg-slate-800/30 text-center">
        <router-link to="/notifications" class="text-xs text-blue-400 hover:text-blue-300 block py-1" @click="isOpen = false">View all</router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useNotificationsStore } from '../../stores/notifications'

const store = useNotificationsStore()
const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggle() {
  isOpen.value = !isOpen.value
}

function handleClickOutside(e: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  store.fetchUnread()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
