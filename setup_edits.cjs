const fs = require('fs');
const path = require('path');

const basePath = 'c:/laragon/www/ai-career-optimization-platform';

// 1. Update resources/js/services/api.ts
const apiPath = path.join(basePath, 'resources/js/services/api.ts');
let apiContent = fs.readFileSync(apiPath, 'utf8');
const newApiFunctions = `
// Notifications
export const getNotifications = () => api.get('/api/v1/notifications')
export const getUnreadNotifications = () => api.get('/api/v1/notifications/unread')
export const markNotificationRead = (id: string) => api.post(\`/api/v1/notifications/\${id}/read\`)
export const markAllNotificationsRead = () => api.post('/api/v1/notifications/read-all')

// Account / Settings
export const getAccount = () => api.get('/api/v1/account')
export const updateAccount = (data: any) => api.put('/api/v1/account', data)
export const changePassword = (data: any) => api.post('/api/v1/account/password', data)
export const uploadAvatar = (fd: FormData) => api.post('/api/v1/account/avatar', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
export const deleteAccount = (data: any) => api.delete('/api/v1/account', { data })

// Resume Versions
export const deleteResumeVersion = (id: number) => api.delete(\`/api/v1/resume-versions/\${id}\`)
export const setMasterVersion = (id: number) => api.post(\`/api/v1/resume-versions/\${id}/set-master\`)

// Cover Letters
export const updateCoverLetter = (id: number, data: any) => api.put(\`/api/v1/cover-letters/\${id}\`, data)
export const exportCoverLetterUrl = (id: number) => \`/api/v1/cover-letters/\${id}/export\`
`;
if (!apiContent.includes('getNotifications')) {
    fs.writeFileSync(apiPath, apiContent + newApiFunctions);
}

// 2. Update router
const routerPath = path.join(basePath, 'resources/js/router/index.ts');
let routerContent = fs.readFileSync(routerPath, 'utf8');
if (!routerContent.includes("name: 'settings'")) {
    const routeInsert = `
        { path: 'settings', name: 'settings', component: () => import('../pages/SettingsPage.vue') },
        { path: 'billing', name: 'billing', component: () => import('../pages/BillingPage.vue') },
        { path: 'notifications', name: 'notifications', component: () => import('../pages/NotificationsPage.vue') },
`;
    routerContent = routerContent.replace(/(children:\s*\[)/, `$1${routeInsert}`);
    fs.writeFileSync(routerPath, routerContent);
}

// 3. Update AppShell
const appShellPath = path.join(basePath, 'resources/js/components/layout/AppShell.vue');
let appShellContent = fs.readFileSync(appShellPath, 'utf8');

if (!appShellContent.includes('NotificationBell')) {
    appShellContent = appShellContent.replace(/<script setup lang="ts">/, '<script setup lang="ts">\nimport NotificationBell from "../ui/NotificationBell.vue"\nimport { onMounted } from "vue"\nimport { useNotificationsStore } from "../../stores/notifications"\n\nconst notificationsStore = useNotificationsStore()\nonMounted(() => {\n  notificationsStore.fetchUnread()\n  setInterval(() => notificationsStore.fetchUnread(), 60000)\n})');
    
    const navItems = `
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
    `;
    appShellContent = appShellContent.replace(/(<\/nav>)/, `${navItems}\n$1`);
    appShellContent = appShellContent.replace(/(<\/nav>)/, `$1\n        <div class="px-4 py-2 mt-auto">\n          <NotificationBell />\n        </div>`);
    
    fs.writeFileSync(appShellPath, appShellContent);
}

console.log('Script completed');
