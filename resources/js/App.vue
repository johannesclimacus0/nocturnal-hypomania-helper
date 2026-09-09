<script setup lang="ts">
import router from './router/router'
import { useAuth } from './stores/auth'
import BaseButton from './components/BaseButton.vue'

const auth = useAuth()

async function retryAuthentication() {
    await auth.refresh()

    if (!auth.unavailable.value) {
        await router.replace(window.location.pathname + window.location.search)
    }
}
</script>

<template>
    <main v-if="auth.unavailable.value">
        <p role="alert">
            Не удалось проверить авторизацию. Проверьте подключение и попробуйте снова.
        </p>
        <BaseButton @click="retryAuthentication">
            Повторить
        </BaseButton>
    </main>
    <RouterView v-else />
</template>
