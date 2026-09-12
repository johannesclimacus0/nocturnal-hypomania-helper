<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../stores/auth'
import AlertMessage from '../../components/AlertMessage.vue'
import BaseButton from '../../components/BaseButton.vue'
import UserLayout from '../../layouts/UserLayout.vue'

const router = useRouter()
const auth = useAuth()
const user = auth.user

const loading = ref(false)
const errorMessage = ref('')

const logout = async function () {
    loading.value = true
    errorMessage.value = ''

    try {
        await auth.logout()
        await router.replace({ name: 'login' })
    } catch {
        errorMessage.value = 'Could not sign out'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <UserLayout>
        <template #user><div v-if="user" class="space-y-1"><p class="truncate text-zinc-200">{{ user.name }}</p></div></template>
        <template #actions><BaseButton :loading="loading" loading-text="Signing out..." @click="logout">logout</BaseButton><AlertMessage :message="errorMessage" /></template>
        <RouterView />
    </UserLayout>
</template>
