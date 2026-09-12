<script setup lang="ts">
import router from './router/router'
import { useAuth } from './stores/auth'
import BaseButton from './components/BaseButton.vue'
import AlertMessage from './components/AlertMessage.vue'

const auth = useAuth()

const retryAuthentication = async function () {
    await auth.refresh()

    if (!auth.unavailable.value) {
        await router.replace(window.location.pathname + window.location.search)
    }
}
</script>

<template>
    <main v-if="auth.unavailable.value" class="mx-auto max-w-xl px-5 py-12">
        <AlertMessage message="Could not verify your session. Check your connection and try again." class="mb-3" />
        <BaseButton @click="retryAuthentication">
            Retry
        </BaseButton>
    </main>
    <RouterView v-else />
</template>
