import { createApp } from 'vue'
import App from './App.vue'
import router from './router/router'
import { useAuth } from './stores/auth'

const auth = useAuth()

auth.initialize().then(() => {
    createApp(App).use(router).mount('#app')
})
