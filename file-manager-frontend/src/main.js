import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import './style.css'
import { useAuthStore } from './stores/auth'
import { useThemeStore } from './stores/theme'

const app = createApp(App)

app.use(createPinia())

useThemeStore().init()

useAuthStore()
  .fetchUser()
  .finally(() => {
    app.use(router)
    app.mount('#app')
  })
