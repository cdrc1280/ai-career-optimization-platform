<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useNotificationsStore } from '../stores/notifications'
import { useRouter } from 'vue-router'
import { useResumesStore }     from '../stores/resumes'
import { useJobPostingsStore }  from '../stores/jobPostings'
import { useApplicationsStore } from '../stores/applications'
import { useProfileStore }      from '../stores/profile'
import { useAnalysisStore }     from '../stores/analysis'
import ProgressBar  from '../components/ui/ProgressBar.vue'
import StatusBadge  from '../components/ui/StatusBadge.vue'

const router       = useRouter()
const resumesStore = useResumesStore()
const jobsStore    = useJobPostingsStore()
const appsStore    = useApplicationsStore()
const profileStore = useProfileStore()
const analysisStore= useAnalysisStore()

const loading = ref(true)

onMounted(async () => {
    await Promise.allSettled([
        resumesStore.fetch(),
        jobsStore.fetch(),
        appsStore.fetch(),
        profileStore.fetch(),
        analysisStore.fetchAll(),
    ])
    loading.value = false
})

const stats = computed(() => [
    {
        label:   'Total Resumes',
        value:   resumesStore.resumes.length,
        icon:    '📄',
        color:   '#3b82f6',
        bg:      'rgba(59,130,246,0.08)',
        to:      '/resumes',
    },
    {
        label:   'Active Applications',
        value:   appsStore.applications.filter(a => !['rejected','accepted','withdrawn'].includes(a.status)).length,
        icon:    '💼',
        color:   '#10b981',
        bg:      'rgba(16,185,129,0.08)',
        to:      '/applications',
    },
    {
        label:   'Analyses Run',
        value:   analysisStore.analyses.length,
        icon:    '🎯',
        color:   '#6366f1',
        bg:      'rgba(99,102,241,0.08)',
        to:      '/analysis',
    },
    {
        label:   'Avg Match Score',
        value:   analysisStore.analyses.length
            ? Math.round(analysisStore.analyses.reduce((a, b) => a + (b.overall_match_score ?? 0), 0) / analysisStore.analyses.length) + '%'
            : '—',
        icon:    '📊',
        color:   '#f59e0b',
        bg:      'rgba(245,158,11,0.08)',
        to:      '/analysis',
    },
])

const completion   = computed(() => profileStore.profile?.completion_percentage ?? 0)
const recentJobs   = computed(() => jobsStore.jobPostings.slice(0, 5))
const recentApps   = computed(() => appsStore.applications.slice(0, 6))

const quickActions = [
    { label: 'Upload Resume',       icon: '📤', to: '/resumes',       color: '#3b82f6' },
    { label: 'Add Job Posting',     icon: '🔍', to: '/job-postings',  color: '#6366f1' },
    { label: 'Generate Cover Letter', icon: '✉️', to: '/cover-letters', color: '#10b981' },
    { label: 'Track Application',   icon: '📌', to: '/applications',  color: '#f59e0b' },
]
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Your career overview at a glance</p>
      </div>
      <div style="display:flex;gap:8px">
        <button class="btn btn-secondary btn-sm" @click="router.push('/resumes')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
          Upload Resume
        </button>
        <button class="btn btn-primary btn-sm" @click="router.push('/job-postings')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Job Posting
        </button>
      </div>
    </div>

    <!-- Skeleton while loading -->
    <template v-if="loading">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
        <div v-for="i in 4" :key="i" class="skeleton" style="height:100px;border-radius:16px" />
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="skeleton" style="height:280px;border-radius:16px" />
        <div class="skeleton" style="height:280px;border-radius:16px" />
      </div>
    </template>

    <template v-else>
      <!-- Stats grid -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px" class="stagger">
        <div
          v-for="s in stats" :key="s.label"
          class="stat-card card-hover"
          style="cursor:pointer"
          @click="router.push(s.to)"
        >
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div>
              <p style="font-size:0.8125rem;color:#94a3b8;font-weight:500">{{ s.label }}</p>
              <p style="font-size:1.875rem;font-weight:800;line-height:1.1;margin-top:6px" :style="{ color: s.color }">
                {{ s.value }}
              </p>
            </div>
            <div style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0" :style="{ background: s.bg }">
              {{ s.icon }}
            </div>
          </div>
        </div>
      </div>

      <!-- Profile completion -->
      <div class="card" style="margin-bottom:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:14px">
          <div>
            <h3 style="font-size:0.9375rem;font-weight:600;color:#e2e8f0">Profile Completion</h3>
            <p style="font-size:0.8125rem;color:#94a3b8;margin-top:3px">A complete profile improves your AI match scores</p>
          </div>
          <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.5rem;font-weight:800;background:linear-gradient(135deg,#3b82f6,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
              {{ completion }}%
            </span>
            <button class="btn btn-secondary btn-sm" @click="router.push('/profile')">Edit Profile</button>
          </div>
        </div>
        <ProgressBar :value="completion" :color="completion >= 80 ? 'success' : completion >= 50 ? 'warning' : 'danger'" />
        <p v-if="completion < 100" style="font-size:0.78rem;color:#4b6080;margin-top:8px">
          {{ completion < 30 ? '🚀 Add work experience and skills to get started.' : completion < 70 ? '⚡ Looking good! Add certifications to stand out.' : '✨ Almost there! Just a few more details.' }}
        </p>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:24px">
        <!-- Recent Job Postings -->
        <div class="card">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="font-size:0.9375rem;font-weight:600;color:#e2e8f0">Recent Job Postings</h3>
            <button class="btn btn-ghost btn-sm" style="color:#3b82f6" @click="router.push('/job-postings')">View all →</button>
          </div>
          <div v-if="recentJobs.length === 0" class="empty-state" style="padding:32px 16px">
            <div class="empty-icon">💼</div>
            <p class="empty-title">No job postings yet</p>
            <p class="empty-desc">Add job postings to start analyzing your resume</p>
            <button class="btn btn-primary btn-sm" style="margin-top:12px" @click="router.push('/job-postings')">Add Job Posting</button>
          </div>
          <div v-else style="display:flex;flex-direction:column;gap:8px">
            <div
              v-for="job in recentJobs" :key="job.id"
              class="card-hover"
              style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.05);cursor:pointer;transition:all 0.15s"
              @click="router.push('/job-postings')"
            >
              <div style="min-width:0">
                <p style="font-size:0.875rem;font-weight:500;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ job.job_title || 'Untitled' }}</p>
                <p style="font-size:0.78rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ job.company_name || '—' }}</p>
              </div>
              <StatusBadge :status="job.extraction_status ?? 'pending'" />
            </div>
          </div>
        </div>

        <!-- Recent Applications -->
        <div class="card">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="font-size:0.9375rem;font-weight:600;color:#e2e8f0">Application Tracker</h3>
            <button class="btn btn-ghost btn-sm" style="color:#3b82f6" @click="router.push('/applications')">View all →</button>
          </div>
          <div v-if="recentApps.length === 0" class="empty-state" style="padding:32px 16px">
            <div class="empty-icon">📋</div>
            <p class="empty-title">No applications tracked</p>
            <p class="empty-desc">Start tracking your job applications here</p>
            <button class="btn btn-primary btn-sm" style="margin-top:12px" @click="router.push('/applications')">Track Application</button>
          </div>
          <div v-else style="display:flex;flex-direction:column;gap:8px">
            <div
              v-for="app in recentApps" :key="app.id"
              class="card-hover"
              style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.05);cursor:pointer;transition:all 0.15s"
              @click="router.push('/applications')"
            >
              <div style="min-width:0">
                <p style="font-size:0.875rem;font-weight:500;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ app.job_title }}</p>
                <p style="font-size:0.78rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ app.company_name }}</p>
              </div>
              <StatusBadge :status="app.status" />
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h3 style="font-size:0.9375rem;font-weight:600;color:#e2e8f0;margin-bottom:14px">Quick Actions</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px">
          <button
            v-for="action in quickActions" :key="action.label"
            style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 12px;border-radius:14px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);cursor:pointer;transition:all 0.2s;font-family:inherit"
            @mouseenter="($event.currentTarget as HTMLElement).style.borderColor = action.color + '40'"
            @mouseleave="($event.currentTarget as HTMLElement).style.borderColor = 'rgba(255,255,255,0.06)'"
            @click="router.push(action.to)"
          >
            <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem" :style="{ background: action.color + '15' }">
              {{ action.icon }}
            </div>
            <span style="font-size:0.8125rem;color:#94a3b8;font-weight:500;text-align:center;line-height:1.3">{{ action.label }}</span>
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
