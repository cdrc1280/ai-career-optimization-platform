import { createApp } from 'vue'
import App from './App.vue'
import '../css/app.css'
import { initAuth } from './services/api'

const app = createApp(App)

;(async () => {
	await initAuth()
	app.mount('#app')
})()
