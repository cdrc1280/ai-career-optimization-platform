const fs = require('fs');
const path = require('path');

const basePath = 'c:/laragon/www/ai-career-optimization-platform';

// ResumeDetailPage
const resumeDetailPath = path.join(basePath, 'resources/js/pages/ResumeDetailPage.vue');
if (fs.existsSync(resumeDetailPath)) {
    let content = fs.readFileSync(resumeDetailPath, 'utf8');
    if (!content.includes('deleteResumeVersion')) {
        content = content.replace(/import {([^}]+)} from '..\/services\/api'/, "import {$1, deleteResumeVersion, setMasterVersion } from '../services/api'");
        content = content.replace(/const route = useRoute\(\)/, `const route = useRoute()
const router = useRouter()
const isDeleting = ref(false)

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this version?')) return
    isDeleting.value = true
    try {
        await deleteResumeVersion(route.params.id as any)
        addToast('success', 'Version deleted')
        router.push('/resumes')
    } catch {
        addToast('error', 'Failed to delete')
    } finally {
        isDeleting.value = false
    }
}

async function handleSetMaster() {
    if (!confirm('Set this as the master version?')) return
    try {
        await setMasterVersion(route.params.id as any)
        addToast('success', 'Set as master version')
        // fetchResume() - assuming it's fetch() or something similar
    } catch {
        addToast('error', 'Failed to set master')
    }
}`);
        const actionsUi = `
        <button v-if="!resume?.is_master" @click="handleSetMaster" class="btn btn-secondary btn-sm" title="Set as Master">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            Set Master
        </button>
        <button v-if="!resume?.is_master" @click="handleDelete" :disabled="isDeleting" class="btn btn-danger btn-sm" title="Delete Version">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Delete
        </button>`;
        content = content.replace(/(<div class="flex items-center gap-2">)/, `$1${actionsUi}`);
        content = content.replace(/<div v-for="\(change, i\) in resume\.change_log" :key="i" class="text-sm">[\s\S]*?<\/div>/g, `
        <div v-for="(change, i) in resume.change_log" :key="i" class="text-sm p-3 glass rounded mb-2">
            <div class="font-semibold text-text-primary mb-1">{{ change.field || 'Change' }}</div>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-2 bg-red-500/10 border border-red-500/20 rounded text-red-200">
                    <div class="text-xs text-red-400 mb-1">Before</div>
                    <del>{{ change.old_value || change.before || 'N/A' }}</del>
                </div>
                <div class="p-2 bg-green-500/10 border border-green-500/20 rounded text-green-200">
                    <div class="text-xs text-green-400 mb-1">After</div>
                    <ins class="no-underline">{{ change.new_value || change.after || 'N/A' }}</ins>
                </div>
            </div>
        </div>`);
        content = content.replace(/@click="exportPdf"/, `@click="() => window.open(\`/api/v1/resume-versions/\${route.params.id}/export?format=pdf\`, '_blank')"`);
        content = content.replace(/@click="exportDocx"/, `@click="() => window.open(\`/api/v1/resume-versions/\${route.params.id}/export?format=docx\`, '_blank')"`);
        fs.writeFileSync(resumeDetailPath, content);
    }
}

// CoverLettersPage
const coverLettersPath = path.join(basePath, 'resources/js/pages/CoverLettersPage.vue');
if (fs.existsSync(coverLettersPath)) {
    let content = fs.readFileSync(coverLettersPath, 'utf8');
    if (!content.includes('exportCoverLetterUrl')) {
        content = content.replace(/import {([^}]+)} from '..\/services\/api'/, "import {$1, updateCoverLetter, exportCoverLetterUrl} from '../services/api'");
        content = content.replace(/const selectedLetter = ref<any>\(null\)/, `const selectedLetter = ref<any>(null)\nlet saveTimeout: any;\n\nasync function handleContentChange(e: Event) {\n    if (!selectedLetter.value) return;\n    const target = e.target as HTMLTextAreaElement;\n    const content = target.value;\n    selectedLetter.value.content = content;\n    clearTimeout(saveTimeout);\n    saveTimeout = setTimeout(async () => {\n        try {\n            await updateCoverLetter(selectedLetter.value.id, { content });\n            addToast('success', 'Saved');\n        } catch { addToast('error', 'Failed to save'); }\n    }, 2000);\n}\n`);
        
        // Add PDF download and delete buttons
        content = content.replace(/<div class="flex items-center gap-2">[\s\S]*?<\/div>/, `
            <div class="flex items-center gap-2">
                <button v-if="selectedLetter" @click="() => window.open(exportCoverLetterUrl(selectedLetter.id) + '?format=pdf', '_blank')" class="btn btn-secondary btn-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download PDF
                </button>
            </div>`);

        // Inline edit layout for cover letter
        content = content.replace(/<div class="prose prose-invert max-w-none">[\s\S]*?<\/div>/, `
            <div class="bg-white text-gray-800 p-8 rounded shadow-lg font-serif" style="min-height: 500px;">
                <textarea class="w-full h-full bg-transparent resize-none outline-none" style="min-height: 400px;" :value="selectedLetter.content" @input="handleContentChange"></textarea>
            </div>`);
        fs.writeFileSync(coverLettersPath, content);
    }
}

// ApplicationsPage
const applicationsPath = path.join(basePath, 'resources/js/pages/ApplicationsPage.vue');
if (fs.existsSync(applicationsPath)) {
    let content = fs.readFileSync(applicationsPath, 'utf8');
    if (!content.includes('job_url')) {
        content = content.replace(/const form = ref\(\{([\s\S]*?)\}\)/, `const form = ref({$1, job_url: '', interview_date: '', follow_up_date: ''})`);
        
        // Add fields to modal
        const extraFields = `
            <div class="form-group">
                <label class="form-label">Job URL <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg></label>
                <input v-model="form.job_url" type="url" class="input" placeholder="https://..." />
            </div>
            <div class="form-group">
                <label class="form-label">Interview Date</label>
                <input v-model="form.interview_date" type="date" class="input" />
            </div>
            <div class="form-group">
                <label class="form-label">Follow-up Date</label>
                <input v-model="form.follow_up_date" type="date" class="input" />
            </div>
        `;
        content = content.replace(/(<div class="form-group">\s*<label class="form-label">Status<\/label>[\s\S]*?<\/div>)/, `$1\n${extraFields}`);
        
        // Match score display on card
        content = content.replace(/<h4 class="font-medium text-text-primary">/, `
            <div class="flex justify-between items-start">
                <h4 class="font-medium text-text-primary">`);
        content = content.replace(/<\/h4>/, `</h4>
                <span v-if="app.match_score" class="badge" :class="app.match_score >= 80 ? 'badge-green' : app.match_score >= 60 ? 'badge-yellow' : 'badge-red'">{{app.match_score}}%</span>
            </div>`);
        fs.writeFileSync(applicationsPath, content);
    }
}

// ProfilePage
const profilePath = path.join(basePath, 'resources/js/pages/ProfilePage.vue');
if (fs.existsSync(profilePath)) {
    let content = fs.readFileSync(profilePath, 'utf8');
    if (!content.includes('Avatar upload')) {
        content = content.replace(/<div class="page-header">/, `
            <div class="flex items-center gap-6 mb-8">
                <div class="relative w-24 h-24 rounded-full overflow-hidden bg-slate-800 flex items-center justify-center border-4 border-slate-700/50 group">
                    <img v-if="accountStore.account?.profile?.avatar_url" :src="accountStore.account.profile.avatar_url" class="w-full h-full object-cover" />
                    <span v-else class="text-3xl font-bold text-slate-400">{{ accountStore.account?.name?.charAt(0) || 'U' }}</span>
                    <label class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                        <svg class="w-6 h-6 text-white mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-[10px] text-white font-medium uppercase tracking-wider">Upload</span>
                        <input type="file" class="hidden" accept="image/*" @change="e => { if(e.target.files?.[0]) accountStore.uploadAvatarFile(e.target.files[0]) }" />
                    </label>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-text-primary">{{ accountStore.account?.name || 'User Profile' }}</h2>
                    <p class="text-text-muted">Manage your personal information</p>
                </div>
            </div>
            <div class="page-header">`);
        fs.writeFileSync(profilePath, content);
    }
}

// DashboardPage
const dashboardPath = path.join(basePath, 'resources/js/pages/DashboardPage.vue');
if (fs.existsSync(dashboardPath)) {
    let content = fs.readFileSync(dashboardPath, 'utf8');
    if (!content.includes('Notifications')) {
        content = content.replace(/import {([^}]+)} from 'vue'/, "import {$1} from 'vue'\nimport { useNotificationsStore } from '../stores/notifications'");
        content = content.replace(/const stats = ref/, `const notifStore = useNotificationsStore()\nconst stats = ref`);
        
        const notifWidget = `
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-text-primary">Recent Notifications</h3>
                    <div class="flex items-center gap-2">
                        <button v-if="notifStore.unreadCount > 0" @click="notifStore.markAllRead" class="text-xs text-blue-400 hover:text-blue-300">Mark all read</button>
                        <router-link to="/notifications" class="text-xs text-slate-400 hover:text-slate-300">View all</router-link>
                    </div>
                </div>
                <div v-if="notifStore.items.filter(n => !n.read_at).length === 0" class="text-center py-6 text-slate-400 text-sm">
                    No new notifications
                </div>
                <div v-else class="space-y-3">
                    <div v-for="n in notifStore.items.filter(n => !n.read_at).slice(0, 3)" :key="n.id" class="p-3 bg-slate-800/50 rounded-lg flex items-start gap-3 cursor-pointer hover:bg-slate-800 transition-colors" @click="notifStore.markRead(n.id)">
                        <div class="mt-1 w-2 h-2 rounded-full bg-blue-500"></div>
                        <div>
                            <div class="text-sm font-medium text-text-primary">{{n.title || n.message}}</div>
                            <div class="text-xs text-slate-400 mt-1">{{n.created_at}}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        content = content.replace(/(<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">)/, `$1\n${notifWidget}`);
        fs.writeFileSync(dashboardPath, content);
    }
}

console.log('Script 2 completed');
