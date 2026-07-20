import axios from 'axios'

const backend = (import.meta.env.VITE_BACKEND_URL as string | undefined) ?? window.location.origin
const api = axios.create({
  baseURL: `${backend}/api/v1`,
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' }
})

const tokenKey = 'apiAuthToken'

function setToken(token: string | null) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
    localStorage.setItem(tokenKey, token)
  } else {
    delete api.defaults.headers.common.Authorization
    localStorage.removeItem(tokenKey)
  }
}

const storedToken = localStorage.getItem(tokenKey)
if (storedToken) {
  setToken(storedToken)
}

function getToken() {
  return localStorage.getItem(tokenKey)
}

// Ensure we request Sanctum CSRF cookie before making stateful requests.
let csrfPromise: Promise<any> | null = null
async function ensureCsrf() {
  if (!csrfPromise) {
    csrfPromise = axios.get(`${backend}/sanctum/csrf-cookie`, { withCredentials: true })
      .then(r => r)
      .catch(e => { csrfPromise = null; throw e })
  }
  return csrfPromise
}

async function prepareRequest() {
  if (!api.defaults.headers.common.Authorization) {
    // Try to load a stored token first
    const stored = getToken()
    if (stored) {
      setToken(stored)
      return
    }

    // Attempt to obtain a token from the backend using the authenticated
    // session cookie (web login). This lets web logins automatically
    // provide a Bearer token to JS clients without manual copying.
    try {
      const r = await axios.get(`${backend}/me/token`, { withCredentials: true })
      if (r.data?.token) {
        setToken(r.data.token)
        return
      }
    } catch (e) {
      // ignore and fall back to CSRf fetch for stateful flows
    }

    await ensureCsrf()
  }
}

api.interceptors.response.use(response => response, error => {
  if (error?.response?.status === 401) {
    error.message = error.response.data?.message || 'Unauthenticated. Please login.'
  }
  return Promise.reject(error)
})

export async function loginApi(payload: { email: string; password: string; remember?: boolean }) {
  // Ensure Sanctum CSRF cookie is present for stateful auth flows
  await ensureCsrf()

  const response = await axios.post(`${backend}/api/login`, payload, {
    withCredentials: true,
    headers: { 'Content-Type': 'application/json' }
  })

  if (response.data?.token) {
    setToken(response.data.token)
  }

  return response
}

export async function registerApi(payload: { name: string; email: string; password: string; password_confirmation: string }) {
  // Ensure Sanctum CSRF cookie is present for stateful auth flows
  await ensureCsrf()

  const response = await axios.post(`${backend}/api/register`, payload, {
    withCredentials: true,
    headers: { 'Content-Type': 'application/json' }
  })

  if (response.data?.token) {
    setToken(response.data.token)
  }

  return response
}

export async function logout() {
  setToken(null)
}

export function isLoggedIn() {
  return Boolean(getToken())
}

export async function getResumes() {
  await prepareRequest()
  return api.get('/resumes')
}

export async function getJobPostings() {
  await prepareRequest()
  return api.get('/job-postings')
}

export async function postResumeAnalysis(payload: any) {
  await prepareRequest()
  return api.post('/resume-analyses', payload)
}

export async function postResumeOptimization(payload: any) {
  await prepareRequest()
  return api.post('/resume-optimizations', payload)
}

export async function postCoverLetter(payload: any) {
  await prepareRequest()
  return api.post('/cover-letters', payload)
}

export async function createApplication(payload: any) {
  await prepareRequest()
  return api.post('/applications', payload)
}

export default api

// Initialize auth for browser pages: try to obtain a token via the
// session cookie (/me/token) so web logins automatically provide a Bearer
// token to the JS client without copying.
export async function initAuth() {
  if (api.defaults.headers.common.Authorization) return
  const stored = getToken()
  if (stored) {
    setToken(stored)
    return
  }

  try {
    const r = await axios.get(`${backend}/me/token`, { withCredentials: true })
    if (r.data?.token) {
      setToken(r.data.token)
    }
  } catch (e) {
    // ignore errors — caller can still call ensureCsrf when needed
  }
}
