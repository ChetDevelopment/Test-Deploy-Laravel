import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'

const app = createApp(App)

window.addEventListener('auth:unauthorized', () => {
  const currentName = router.currentRoute?.value?.name
  if (currentName === 'login') return

  router.push({ name: 'login' }).catch(() => {
    // Ignore duplicate navigation errors.
  })
})

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Vue Error:', err)
  console.error('Component:', vm)
  console.error('Info:', info)
}

// Mount app
app.use(router)
app.mount('#app')
