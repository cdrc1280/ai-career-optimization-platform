<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useProfileStore } from '../stores/profile'
import ProgressBar from '../components/ui/ProgressBar.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import { addToast } from '../composables/toast'
import PageLayout from '../components/layout/PageLayout.vue'

const profileStore = useProfileStore()
const saving = ref<string | null>(null)

onMounted(() => profileStore.fetch())

const profile = computed(() => profileStore.profile ?? {})

// Section form data (reactive, updated when profile loads)
const personal = reactive({ full_name: '', professional_title: '', phone: '', location: '', linkedin_url: '', github_url: '', portfolio_url: '' })
const career = reactive({ years_of_experience: '', preferred_roles: [] as string[], career_goals: '', employment_type: '' })
const newSkill = ref('')
const newRole = ref('')

function loadForms() {
    const p = profile.value
    Object.assign(personal, {
        full_name: p.full_name ?? '', professional_title: p.professional_title ?? '',
        phone: p.phone ?? '', location: p.location ?? '',
        linkedin_url: p.linkedin_url ?? '', github_url: p.github_url ?? '', portfolio_url: p.portfolio_url ?? ''
    })
    Object.assign(career, {
        years_of_experience: p.years_of_experience ?? '',
        preferred_roles: p.preferred_roles ?? [],
        career_goals: p.career_goals ?? '',
        employment_type: p.employment_type ?? '',
    })
}

// Watch for profile load
import { watch } from 'vue'
watch(() => profileStore.profile, () => loadForms(), { immediate: true })

async function saveSection(section: string, data: any) {
    saving.value = section
    try {
        await profileStore.update(data)
        addToast('success', 'Profile updated!')
    } catch (e: any) {
        addToast('error', e?.response?.data?.message ?? 'Failed to save.')
    } finally { saving.value = null }
}

function addRole() {
    if (newRole.value.trim() && !career.preferred_roles.includes(newRole.value.trim())) {
        career.preferred_roles.push(newRole.value.trim())
        newRole.value = ''
    }
}

function removeRole(role: string) {
    career.preferred_roles = career.preferred_roles.filter(r => r !== role)
}

const completion = computed(() => profileStore.profile?.completion_percentage ?? 0)
</script>

<template>
  <PageLayout title="My Profile" subtitle="Keep your profile up-to-date for better AI analysis results">
    <!-- Completion bar -->
    <div class="card">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-white text-sm">Profile Completion</h3>
        <span class="font-bold gradient-text">{{ completion }}%</span>
      </div>
      <ProgressBar :value="completion" :color="completion >= 80 ? 'success' : completion >= 50 ? 'warning' : 'danger'" />
    </div>

    <div v-if="profileStore.loading" class="flex justify-center py-12"><LoadingSpinner :size="32" /></div>

    <template v-else>
      <!-- Personal Information -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">👤 Personal Information</h3>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs text-slate-400 mb-1">Full Name</label>
            <input v-model="personal.full_name" class="input" placeholder="John Doe" />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">Professional Title</label>
            <input v-model="personal.professional_title" class="input" placeholder="Senior Software Engineer" />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">Phone</label>
            <input v-model="personal.phone" class="input" placeholder="+63 912 345 6789" />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">Location</label>
            <input v-model="personal.location" class="input" placeholder="Manila, Philippines" />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">LinkedIn URL</label>
            <input v-model="personal.linkedin_url" class="input" placeholder="https://linkedin.com/in/..." />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">GitHub URL</label>
            <input v-model="personal.github_url" class="input" placeholder="https://github.com/..." />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs text-slate-400 mb-1">Portfolio URL</label>
            <input v-model="personal.portfolio_url" class="input" placeholder="https://yourwebsite.dev" />
          </div>
        </div>
        <div class="flex justify-end">
          <button class="btn btn-primary btn-sm" :disabled="saving === 'personal'" @click="saveSection('personal', personal)">
            <LoadingSpinner v-if="saving === 'personal'" :size="12" />
            Save Personal Info
          </button>
        </div>
      </div>

      <!-- Career Goals -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">🎯 Career Goals</h3>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs text-slate-400 mb-1">Years of Experience</label>
            <input v-model="career.years_of_experience" type="number" min="0" max="60" class="input" placeholder="5" />
          </div>
          <div>
            <label class="block text-xs text-slate-400 mb-1">Employment Type</label>
            <select v-model="career.employment_type" class="input">
              <option value="">Any</option>
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="freelance">Freelance</option>
              <option value="internship">Internship</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs text-slate-400 mb-1">Preferred Roles</label>
            <div class="flex flex-wrap gap-2 mb-2">
              <span v-for="role in career.preferred_roles" :key="role" class="badge badge-blue flex items-center gap-1">
                {{ role }}
                <button class="hover:text-red-300 ml-1" @click="removeRole(role)">×</button>
              </span>
            </div>
            <div class="flex gap-2">
              <input v-model="newRole" class="input flex-1" placeholder="e.g. Backend Developer" @keyup.enter="addRole" />
              <button class="btn btn-secondary btn-sm flex-shrink-0" @click="addRole">Add</button>
            </div>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs text-slate-400 mb-1">Career Goals</label>
            <textarea v-model="career.career_goals" class="input" rows="3" placeholder="Describe your career objectives..." />
          </div>
        </div>
        <div class="flex justify-end">
          <button class="btn btn-primary btn-sm" :disabled="saving === 'career'" @click="saveSection('career', career)">
            <LoadingSpinner v-if="saving === 'career'" :size="12" />
            Save Career Goals
          </button>
        </div>
      </div>

      <!-- Skills -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">🛠️ Skills</h3>
        <div class="flex flex-wrap gap-2 mb-4">
          <span v-for="skill in profile.skills ?? []" :key="skill.id ?? skill.name" class="badge badge-indigo">
            {{ skill.name }}
          </span>
          <span v-if="!(profile.skills?.length)" class="text-slate-500 text-sm">No skills added yet. Upload a resume to auto-import your skills.</span>
        </div>
        <p class="text-slate-500 text-xs">Skills are automatically imported when you upload and parse a resume.</p>
      </div>

      <!-- Work Experience -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">💼 Work Experience</h3>
        <div v-if="!(profile.work_experiences?.length)" class="text-center py-8 text-slate-500">
          <p class="text-sm">Work experience will be auto-imported from your resume.</p>
        </div>
        <div v-else class="space-y-4">
          <div v-for="exp in profile.work_experiences" :key="exp.id" class="p-4 rounded-xl glass border border-white/06">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h4 class="font-semibold text-white">{{ exp.title }} <span class="text-slate-400">at</span> {{ exp.company }}</h4>
                <p class="text-blue-400 text-xs mt-0.5">{{ exp.start_date }} – {{ exp.is_current ? 'Present' : exp.end_date }}</p>
                <p v-if="exp.location" class="text-slate-400 text-xs">{{ exp.location }}</p>
                <ul v-if="exp.responsibilities?.length" class="mt-2 space-y-0.5">
                  <li v-for="(r, i) in exp.responsibilities.slice(0, 3)" :key="i" class="text-slate-300 text-xs flex gap-1.5">
                    <span class="text-blue-400 flex-shrink-0">•</span> {{ r }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Education -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">🎓 Education</h3>
        <div v-if="!(profile.educations?.length)" class="text-center py-6 text-slate-500 text-sm">
          Education will be auto-imported from your resume.
        </div>
        <div v-else class="space-y-3">
          <div v-for="edu in profile.educations" :key="edu.id" class="p-4 rounded-xl glass border border-white/06">
            <h4 class="font-semibold text-white text-sm">{{ edu.degree }} in {{ edu.field_of_study }}</h4>
            <p class="text-blue-400 text-xs mt-0.5">{{ edu.institution }}</p>
            <p class="text-slate-400 text-xs">{{ edu.start_date }} – {{ edu.end_date }}</p>
          </div>
        </div>
      </div>

      <!-- Certifications -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">🏆 Certifications</h3>
        <div v-if="!(profile.certifications?.length)" class="text-center py-6 text-slate-500 text-sm">
          Certifications will be auto-imported from your resume.
        </div>
        <div v-else class="space-y-3">
          <div v-for="cert in profile.certifications" :key="cert.id" class="p-3 rounded-xl glass border border-white/06 flex items-center gap-3">
            <span class="text-yellow-400 text-lg">🎖️</span>
            <div>
              <p class="font-medium text-white text-sm">{{ cert.name }}</p>
              <p class="text-slate-400 text-xs">{{ cert.issuing_organization }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Languages -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">🌐 Languages</h3>
        <div v-if="!(profile.languages?.length)" class="text-center py-6 text-slate-500 text-sm">
          Languages will be auto-imported from your resume.
        </div>
        <div v-else class="flex flex-wrap gap-3">
          <div v-for="lang in profile.languages" :key="lang.id" class="flex items-center gap-2 p-3 rounded-xl glass border border-white/06">
            <span class="text-lg">🌐</span>
            <div>
              <p class="font-medium text-white text-sm">{{ lang.name }}</p>
              <p class="text-xs text-slate-400 capitalize">{{ lang.proficiency }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Projects -->
      <div class="card">
        <h3 class="font-semibold text-white mb-4">🚀 Projects</h3>
        <div v-if="!(profile.projects?.length)" class="text-center py-6 text-slate-500 text-sm">
          Projects will be auto-imported from your resume.
        </div>
        <div v-else class="grid sm:grid-cols-2 gap-4">
          <div v-for="proj in profile.projects" :key="proj.id" class="p-4 rounded-xl glass border border-white/06 hover:border-blue-500/30 transition-all">
            <h4 class="font-semibold text-white text-sm mb-1">{{ proj.name }}</h4>
            <p class="text-slate-400 text-xs mb-2 leading-relaxed">{{ proj.description }}</p>
            <div class="flex flex-wrap gap-1">
              <span v-for="tech in (proj.technologies ?? [])" :key="tech" class="badge badge-gray text-[10px]">{{ tech }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </PageLayout>
</template>
