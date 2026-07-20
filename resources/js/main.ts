import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import '../css/app.css'
import { initAuth, isLoggedIn } from './services/api'
import { useAuthStore } from './stores/auth'

const pinia = createPinia()
const app   = createApp(App)

// Global error handler — prevents the whole app from crashing on unhandled promise rejections
app.config.errorHandler = (err, _vm, info) => {
    console.error('[Vue error]', info, err)
}

app.use(pinia)
app.use(router)

;(async () => {
    try {
        const result = await initAuth()
        if (result.authenticated && result.user) {
            const authStore = useAuthStore()
            authStore.setUser(result.user)
        }
    } catch (e) {
        console.warn('[initAuth] failed silently:', e)
    }

    app.mount('#app-vue')
})()
