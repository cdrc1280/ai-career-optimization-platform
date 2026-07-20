<script setup lang="ts">
import NotificationBell from "../ui/NotificationBell.vue"
import { onMounted } from "vue"
import { useNotificationsStore } from "../../stores/notifications"

const notificationsStore = useNotificationsStore()
onMounted(() => {
  notificationsStore.fetchUnread()
  setInterval(() => notificationsStore.fetchUnread(), 60000)
})
import { ref } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { logout } from '../../services/api'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const sidebarOpen = ref(false)

interface NavItem {
    name: string
    to: string
    icon: string
}

const navGroups = [
    {
        label: 'Overview',
        items: [
            { name: 'Dashboard',    to: '/dashboard',  icon: 'grid' },
            { name: 'Profile',      to: '/profile',    icon: 'user' },
        ],
    },
    {
        label: 'Career Tools',
        items: [
            { name: 'Resumes',          to: '/resumes',       icon: 'document' },
            { name: 'Job Postings',     to: '/job-postings',  icon: 'briefcase' },
            { name: 'Analysis',         to: '/analysis',      icon: 'chart' },
            { name: 'Cover Letters',    to: '/cover-letters', icon: 'mail' },
        ],
    },
    {
        label: 'Growth',
        items: [
            { name: 'Interview Prep',   to: '/interview-prep',         icon: 'mic' },
            { name: 'Career Paths',     to: '/career-recommendations', icon: 'compass' },
            { name: 'Applications',     to: '/applications',           icon: 'kanban' },
        ],
    },
]

const PATHS: Record<string, string> = {
    grid:     'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z',
    user:     'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z',
    document: 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8',
    briefcase:'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    chart:    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    mail:     'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    mic:      'M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3zM19 10v2a7 7 0 01-14 0v-2M12 19v4M8 23h8',
    compass:  'M12 2a10 10 0 100 20A10 10 0 0012 2zm0 0v20M2 12h20M4.93 4.93l14.14 14.14M19.07 4.93L4.93 19.07',
    kanban:   'M3 3h5v5H3zM3 10h5v11H3zM10 3h5v11h-5zM16 3h5v5h-5zM16 10h5v11h-5zM10 16h5v5h-5z',
}

function isActive(to: string) {
    return route.path === to || (to !== '/dashboard' && route.path.startsWith(to))
}

async function handleLogout() {
    await logout()
}
</script>

<template>
  <!-- Mobile overlay -->
  <Transition name="fade">
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm"
      style="backdrop-filter:blur(4px)"
      @click="sidebarOpen = false"
    />
  </Transition>

  <!-- ─── Sidebar ─────────────────────────────────────────────────── -->
  <aside :class="['sidebar', { open: sidebarOpen }]">
    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
      </div>
      <div>
        <div style="font-size:0.9375rem;font-weight:700;color:#e2e8f0;line-height:1">CareerAI</div>
        <div style="font-size:10px;font-weight:600;letter-spacing:0.08em;color:#3b82f6;margin-top:2px">PLATFORM</div>
      </div>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav">
      <template v-for="group in navGroups" :key="group.label">
        <div class="nav-section-label">{{ group.label }}</div>
        <RouterLink
          v-for="item in group.items"
          :key="item.to"
          :to="item.to"
          :class="['nav-item', { active: isActive(item.to) }]"
          @click="sidebarOpen = false"
        >
          <svg class="nav-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path :d="PATHS[item.icon]" />
          </svg>
          {{ item.name }}
        </RouterLink>
      </template>
    
        <div class="mt-4 pt-4 border-t border-slate-700/50">
            <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Account</h3>
            <router-link to="/settings" class="nav-item" active-class="active">
                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Settings
            </router-link>
            <router-link to="/billing" class="nav-item" active-class="active">
                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                Billing
            </router-link>
        </div>
    
</nav>
        <div class="px-4 py-2 mt-auto">
          <NotificationBell />
        </div>

    <!-- User -->
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar">{{ auth.initials }}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:0.875rem;font-weight:500;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth.displayName }}</div>
          <div style="font-size:0.75rem;color:#4b6080;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth.displayEmail }}</div>
        </div>
        <button
          class="btn btn-ghost btn-icon"
          title="Sign out"
          style="flex-shrink:0"
          @click="handleLogout"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
          </svg>
        </button>
      </div>
    </div>
  </aside>

  <!-- ─── Main content ────────────────────────────────────────────── -->
  <div class="main-content">
    <!-- Mobile topbar -->
    <div class="topbar">
      <button class="btn btn-ghost btn-icon" @click="sidebarOpen = true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
      </button>
      <span style="font-size:1rem;font-weight:700;background:linear-gradient(135deg,#3b82f6,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">CareerAI</span>
    </div>

    <!-- Page content with transition -->
    <div class="page-content">
      <RouterView v-slot="{ Component, route: r }">
        <Transition name="page" mode="out-in">
          <component :is="Component" :key="r.path" />
        </Transition>
      </RouterView>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.page-enter-active { animation: page-in 0.28s ease both; }
.page-leave-active { animation: page-out 0.18s ease both; }

@keyframes page-in  { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes page-out { from { opacity: 1; } to { opacity: 0; } }
</style>
