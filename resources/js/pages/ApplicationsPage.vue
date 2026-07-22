<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApplicationsStore } from '../stores/applications'
import { useJobPostingsStore } from '../stores/jobPostings'
import Modal from '../components/ui/Modal.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { addToast } from '../composables/toast'
import PageLayout from '../components/layout/PageLayout.vue'

const appsStore = useApplicationsStore()
const jobsStore = useJobPostingsStore()

const view = ref<'kanban' | 'list'>('kanban')
const showAddModal = ref(false)
const showDetailModal = ref(false)
const selectedApp = ref<any>(null)
const searchQuery = ref('')

const form = ref({ company_name: '', job_title: '', job_url: '', notes: '', status: 'saved' })

const STATUSES = ['saved', 'applied', 'assessment', 'interview', 'offer', 'accepted', 'rejected', 'withdrawn']
const KANBAN_COLS = [
    { key: 'saved',       label: '📌 Saved',      color: 'text-slate-400' },
    { key: 'applied',     label: '📤 Applied',     color: 'text-blue-400' },
    { key: 'assessment',  label: '📝 Assessment',  color: 'text-indigo-400' },
    { key: 'interview',   label: '🎤 Interview',   color: 'text-yellow-400' },
    { key: 'offer',       label: '🎉 Offer',       color: 'text-green-400' },
]

onMounted(async () => {
    await Promise.all([appsStore.fetch(), jobsStore.fetch()])
})

const filtered = computed(() =>
    appsStore.applications.filter(a =>
        !searchQuery.value || `${a.company_name} ${a.job_title}`.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
)

function appsForStatus(status: string) {
    return filtered.value.filter(a => a.status === status)
}

function openAdd() {
    showAddModal.value = true
}

async function addApp() {
    try {
        await appsStore.create(form.value)
        addToast('success', 'Application added!')
        showAddModal.value = false
        form.value = { company_name: '', job_title: '', job_url: '', notes: '', status: 'saved' }
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Failed to add.')
    }
}

function openDetail(app: any) {
    selectedApp.value = { ...app }
    showDetailModal.value = true
}

async function updateStatus(app: any, status: string) {
    await appsStore.updateStatus(app.id, status)
    addToast('success', `Status updated to ${status}.`)
}

async function saveDetail() {
    await appsStore.update(selectedApp.value.id, {
        notes: selectedApp.value.notes,
        status: selectedApp.value.status,
    })
    addToast('success', 'Application updated.')
    showDetailModal.value = false
}

async function removeApp(id: number) {
    await appsStore.remove(id)
    showDetailModal.value = false
    addToast('success', 'Application removed.')
}
</script>

<template>
  <PageLayout title="Job Applications" subtitle="Track your job applications through the hiring pipeline">
    <template #actions>
      <button class="btn btn-primary" @click="openAdd">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Application
      </button>
    </template>

    <div class="space-y-6">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <input v-model="searchQuery" type="text" class="input max-w-xs" placeholder="Search applications..." />
        <div class="flex gap-2">
          <button :class="['btn btn-sm', view === 'kanban' ? 'btn-primary' : 'btn-secondary']" @click="view = 'kanban'">⊞ Kanban</button>
          <button :class="['btn btn-sm', view === 'list'   ? 'btn-primary' : 'btn-secondary']" @click="view = 'list'">≡ List</button>
        </div>
      </div>

      <!-- Kanban board -->
      <div v-if="view === 'kanban'" class="overflow-x-auto pb-4">
        <div class="flex gap-4" style="min-width: max-content">
          <div v-for="col in KANBAN_COLS" :key="col.key" class="kanban-column" style="width: 250px">
            <div class="flex items-center justify-between mb-3">
              <h3 :class="['text-sm font-semibold', col.color]">{{ col.label }}</h3>
              <span class="badge badge-gray text-[10px]">{{ appsForStatus(col.key).length }}</span>
            </div>
            <div class="space-y-2 min-h-20">
              <div v-for="app in appsForStatus(col.key)" :key="app.id" class="kanban-card" @click="openDetail(app)">
                <p class="font-medium text-white text-sm truncate">{{ app.job_title }}</p>
                <p class="text-slate-400 text-xs truncate">{{ app.company_name }}</p>
                <p v-if="app.applied_date" class="text-slate-500 text-[10px] mt-1">
                  {{ new Date(app.applied_date).toLocaleDateString() }}
                </p>
                <div v-if="app.notes" class="text-slate-500 text-[10px] mt-1 truncate">{{ app.notes }}</div>
              </div>
              <div v-if="appsForStatus(col.key).length === 0" class="text-center py-4 text-slate-600 text-xs">Empty</div>
            </div>
          </div>
        </div>
      </div>

      <!-- List view -->
      <div v-else class="card">
        <div v-if="appsStore.loading" class="flex justify-center py-8"><LoadingSpinner :size="28" /></div>
        <div v-else-if="filtered.length === 0" class="text-center py-12 text-slate-500">
          <div class="text-4xl mb-3">📋</div>
          <p>No applications yet. Add your first application!</p>
        </div>
        <div v-else class="space-y-2">
          <div v-for="app in filtered" :key="app.id"
            class="flex items-center justify-between p-4 rounded-xl glass-hover border border-white/05 cursor-pointer"
            @click="openDetail(app)">
            <div class="min-w-0">
              <p class="font-medium text-white text-sm truncate">{{ app.job_title }}</p>
              <p class="text-slate-400 text-xs truncate">{{ app.company_name }}</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
              <StatusBadge :status="app.status" />
              <span class="text-slate-500 text-xs">{{ app.applied_date ? new Date(app.applied_date).toLocaleDateString() : '' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add modal -->
    <Modal v-if="showAddModal" title="Add Application" @close="showAddModal = false">
      <div class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-slate-400 mb-1">Company Name</label>
            <input v-model="form.company_name" class="input" placeholder="Google" />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">Job Title</label>
            <input v-model="form.job_title" class="input" placeholder="Software Engineer" />
          </div>
        </div>
        <div>
          <label class="block text-xs text-slate-400 mb-1">Job URL (optional)</label>
          <input v-model="form.job_url" type="url" class="input" placeholder="https://..." />
        </div>
        <div>
          <label class="block text-xs text-slate-400 mb-1">Status</label>
          <select v-model="form.status" class="input">
            <option v-for="s in STATUSES" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-400 mb-1">Notes</label>
          <textarea v-model="form.notes" class="input" rows="3" placeholder="Any notes about this application..." />
        </div>
        <div class="flex gap-2 justify-end pt-2">
          <button class="btn btn-secondary" @click="showAddModal = false">Cancel</button>
          <button class="btn btn-primary" @click="addApp">Add Application</button>
        </div>
      </div>
    </Modal>

    <!-- Detail modal -->
    <ConfirmModal v-if="showDetailModal && selectedApp" :title="`${selectedApp.job_title} at ${selectedApp.company_name}`" @close="showDetailModal = false" @confirm="removeApp(selectedApp.id)" confirmText="Delete">
      <div class="space-y-4">
        <div>
          <label class="block text-xs text-slate-400 mb-1">Status</label>
          <select v-model="selectedApp.status" class="input" @change="updateStatus(selectedApp, selectedApp.status)">
            <option v-for="s in STATUSES" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-400 mb-1">Notes</label>
          <textarea v-model="selectedApp.notes" class="input" rows="6" placeholder="Add notes, follow-up tasks, etc." />
        </div>
        <div class="flex gap-2 justify-end pt-2">
          <button class="btn btn-secondary" @click="showDetailModal = false">Cancel</button>
          <button class="btn btn-primary" @click="saveDetail">Save</button>
        </div>
      </div>
    </ConfirmModal>
  </PageLayout>
</template>
