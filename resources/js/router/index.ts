import { createRouter, createWebHistory } from 'vue-router'
import { isLoggedIn } from '../services/api'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: () => import('../pages/LoginPage.vue'), meta: { guest: true } },
        { path: '/register', name: 'register', component: () => import('../pages/RegisterPage.vue'), meta: { guest: true } },
        { path: '/', redirect: '/dashboard' },
        {
            path: '/',
            component: () => import('../components/layout/AppShell.vue'),
            meta: { requiresAuth: true },
            children: [
        { path: 'settings', name: 'settings', component: () => import('../pages/SettingsPage.vue') },
        { path: 'billing', name: 'billing', component: () => import('../pages/BillingPage.vue') },
        { path: 'notifications', name: 'notifications', component: () => import('../pages/NotificationsPage.vue') },

                { path: 'dashboard', name: 'dashboard', component: () => import('../pages/DashboardPage.vue') },
                { path: 'profile', name: 'profile', component: () => import('../pages/ProfilePage.vue') },
                { path: 'resumes', name: 'resumes', component: () => import('../pages/ResumesPage.vue') },
                { path: 'resumes/:id', name: 'resume-detail', component: () => import('../pages/ResumeDetailPage.vue') },
                { path: 'job-postings', name: 'job-postings', component: () => import('../pages/JobPostingsPage.vue') },
                { path: 'analysis', name: 'analysis', component: () => import('../pages/AnalysisPage.vue') },
                { path: 'cover-letters', name: 'cover-letters', component: () => import('../pages/CoverLettersPage.vue') },
                { path: 'applications', name: 'applications', component: () => import('../pages/ApplicationsPage.vue') },
                { path: 'interview-prep', name: 'interview-prep', component: () => import('../pages/InterviewPrepPage.vue') },
                { path: 'career-recommendations', name: 'career-recs', component: () => import('../pages/CareerRecommendationsPage.vue') },
            ],
        },
    ],
})

router.beforeEach((to) => {
    const loggedIn = isLoggedIn()
    if (to.meta.requiresAuth && !loggedIn) return { name: 'login' }
    if (to.meta.guest && loggedIn) return { name: 'dashboard' }
})

export default router
