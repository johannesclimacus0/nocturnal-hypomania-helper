import { configureEcho } from '@laravel/echo-vue';
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/router'
import { useAuth } from './stores/auth'
import { initializeFontScale } from './composables/useFontScale'
import http from './api/http'

configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),

    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel) => ({
        authorize: (socketId, callback) => {
            http.post('/broadcasting/auth', {
                socket_id: socketId,
                channel_name: channel.name,
            }).then(response => callback(null, response.data))
                .catch(error => callback(error, null))
        },
    }),
});

initializeFontScale()

const auth = useAuth()

auth.initialize().then(() => {
    createApp(App).use(router).mount('#app')
})
