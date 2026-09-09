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
        errorMessage.value = 'Не удалось выйти с аккаунта'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <UserLayout title="Главная">
        <template #actions>
            <BaseButton :loading="loading" loading-text="Выходим..." @click="logout">
                Выйти
            </BaseButton>
        </template>

        <section v-if="user">
            <p>Вы вошли как {{ user.name }}</p>
            <p>{{ user.email }}</p>
        </section>
        <AlertMessage :message="errorMessage" />
    </UserLayout>
</template>
