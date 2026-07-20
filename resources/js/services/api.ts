import axios from 'axios'

// ── Axios instance ─────────────────────────────────────────────────────────────
const api = axios.create({
    baseURL: '/',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':           'application/json',
        'Content-Type':     'application/json',
    },
})

// ── Token management ──────────────────────────────────────────────────────────
const TOKEN_KEY = 'acp_token'
let _token: string | null = localStorage.getItem(TOKEN_KEY)

export function setToken(token: string | null) {
    _token = token
    if (token) {
        localStorage.setItem(TOKEN_KEY, token)
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    } else {
        localStorage.removeItem(TOKEN_KEY)
        delete api.defaults.headers.common['Authorization']
    }
}

// Restore token on page load
if (_token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${_token}`
}

export function isLoggedIn(): boolean {
    return !!_token
}

export function clearToken() {
    setToken(null)
}

// ── CSRF cookie ───────────────────────────────────────────────────────────────
let _csrfFetched = false
async function ensureCsrf() {
    if (_csrfFetched) return
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        _csrfFetched = true
    } catch {
        // Non-fatal; cookies may already be set
    }
}

// ── Response interceptor — auto-clear token on 401 ───────────────────────────
api.interceptors.response.use(
    r => r,
    err => {
        if (err?.response?.status === 401) {
            setToken(null)
        }
        return Promise.reject(err)
    }
)

// ── Auth bootstrap ────────────────────────────────────────────────────────────
export async function initAuth(): Promise<{ authenticated: boolean; user?: any }> {
    await ensureCsrf()

    // If we already have a stored token, trust it and return immediately
    if (_token) {
        return { authenticated: true }
    }

    // Try to retrieve a token from the Laravel session (works when the user
    // navigated to the app via a server-side login / OAuth redirect)
    try {
        const res = await api.get('/me/token')
        if (res.data?.token) {
            setToken(res.data.token)
            return { authenticated: true, user: res.data.user }
        }
    } catch {
        // Not authenticated via session — that's fine, show login page
    }
    return { authenticated: false }
}

// ── Auth endpoints ────────────────────────────────────────────────────────────
export async function loginApi(data: { email: string; password: string }) {
    await ensureCsrf()
    const res = await api.post('/api/login', data)
    if (res.data?.token) setToken(res.data.token)
    return res
}

export async function registerApi(data: {
    name: string
    email: string
    password: string
    password_confirmation: string
}) {
    await ensureCsrf()
    const res = await api.post('/api/register', data)
    if (res.data?.token) setToken(res.data.token)
    return res
}

export async function logout() {
    try {
        await api.post('/logout')
    } catch { /* ignore */ }
    setToken(null)
    _csrfFetched = false
    window.location.href = '/login'
}

// ── Profile ───────────────────────────────────────────────────────────────────
export const getProfile    = ()        => api.get('/api/v1/profile')
export const updateProfile = (d: any)  => api.put('/api/v1/profile', d)

// ── Resumes ───────────────────────────────────────────────────────────────────
export const getResumes    = ()           => api.get('/api/v1/resumes')
export const getResume     = (id: number) => api.get(`/api/v1/resumes/${id}`)
export const uploadResume  = (fd: FormData) =>
    api.post('/api/v1/resumes', fd)
export const deleteResume  = (id: number) => api.delete(`/api/v1/resumes/${id}`)

export const updateResumeVersion  = (vid: number, d: any) => api.put(`/api/v1/resume-versions/${vid}`, d)
export const compareResumeVersion = (vid: number)         => api.get(`/api/v1/resume-versions/${vid}/compare`)
export const exportResumeVersion  = (vid: number, fmt: string) =>
    `/api/v1/resume-versions/${vid}/export/${fmt}`

// ── Job Postings ───────────────────────────────────────────────────────────────
export const getJobPostings    = ()        => api.get('/api/v1/job-postings')
export const createJobPosting  = (d: any)  => api.post('/api/v1/job-postings', d)
export const deleteJobPosting  = (id: number) => api.delete(`/api/v1/job-postings/${id}`)

// ── Analyses ───────────────────────────────────────────────────────────────────
export const getAnalyses    = () => api.get('/api/v1/resume-analyses')
export const triggerAnalysis = (rvId: number, jpId: number) =>
    api.post('/api/v1/resume-analyses', { resume_version_id: rvId, job_posting_id: jpId })
export const getAnalysis     = (rvId: number, jpId: number) =>
    api.get(`/api/v1/resume-analyses/${rvId}/${jpId}`)

// ── Optimization ───────────────────────────────────────────────────────────────
export const triggerOptimization = (d: { resume_version_id: number; job_posting_id: number; instructions?: string }) =>
    api.post('/api/v1/resume-optimizations', d)

// ── Cover Letters ──────────────────────────────────────────────────────────────
export const getCoverLetters    = ()        => api.get('/api/v1/cover-letters')
export const generateCoverLetter = (d: { resume_version_id: number; job_posting_id: number; tone: string }) =>
    api.post('/api/v1/cover-letters', d)

// ── Applications ───────────────────────────────────────────────────────────────
export const getApplications    = ()              => api.get('/api/v1/applications')
export const createApplication  = (d: any)        => api.post('/api/v1/applications', d)
export const updateApplication  = (id: number, d: any) => api.put(`/api/v1/applications/${id}`, d)
export const deleteApplication  = (id: number)    => api.delete(`/api/v1/applications/${id}`)

export default api

// Notifications
export const getNotifications = () => api.get('/api/v1/notifications')
export const getUnreadNotifications = () => api.get('/api/v1/notifications/unread')
export const markNotificationRead = (id: string) => api.post(`/api/v1/notifications/${id}/read`)
export const markAllNotificationsRead = () => api.post('/api/v1/notifications/read-all')

// Account / Settings
export const getPlans = () => api.get('/api/v1/plans')
export const simulateCheckout = (planId: number) => api.post('/api/v1/subscriptions/simulate-checkout', { plan_id: planId })
export const getAccount = () => api.get('/api/v1/account')
export const updateAccount = (data: any) => api.put('/api/v1/account', data)
export const changePassword = (data: any) => api.post('/api/v1/account/password', data)
export const uploadAvatar = (fd: FormData) => api.post('/api/v1/account/avatar', fd)
export const deleteAccount = (data: any) => api.delete('/api/v1/account', { data })

// Resume Versions
export const deleteResumeVersion = (id: number) => api.delete(`/api/v1/resume-versions/${id}`)
export const setMasterVersion = (id: number) => api.post(`/api/v1/resume-versions/${id}/set-master`)

// Cover Letters
export const updateCoverLetter = (id: number, data: any) => api.put(`/api/v1/cover-letters/${id}`, data)
export const exportCoverLetterUrl = (id: number) => `/api/v1/cover-letters/${id}/export`
