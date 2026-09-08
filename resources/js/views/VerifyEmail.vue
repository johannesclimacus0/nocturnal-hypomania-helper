<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { resendVerificationEmail } from '../api/auth'
import { useAuth } from '../stores/auth'

const router = useRouter()
const auth = useAuth()

const sent = ref(false)
const loading = ref(false)
const logoutLoading = ref(false)
const errorMessage = ref('')

async function resend() {
    sent.value = false
    errorMessage.value = ''
    loading.value = true

    try {
        await resendVerificationEmail()
        sent.value = true
    } catch (error: unknown) {
        if (axios.isAxiosError(error)) {
            errorMessage.value =
                error.response?.data?.message ??
                'Не удалось отправить сообщение'
        } else {
            errorMessage.value = 'Произошла неизвестная ошибка'
        }
    } finally {
        loading.value = false
    }
}

async function submitLogout() {
    errorMessage.value = ''
    logoutLoading.value = true
    try {
        await auth.logout()
        await router.push({ name: 'register' })
    } catch {
        errorMessage.value = 'Не удалось выйти из аккаунта'
    } finally {
        logoutLoading.value = false
    }
}
</script>

<template>
    <main>
        <h2>Подтвердите почту</h2>
        <p v-if="auth.user.value">
            Письмо отправлено на почту: {{ auth.user.value.email }}.
        </p>
        <button type="button" :disabled="loading" @click="resend">
            {{ loading ? 'Отправляем...' : 'Отправить снова' }}
        </button>
        <button type="button" :disabled="logoutLoading" @click="submitLogout">
            {{ logoutLoading ? 'Выходим...' : 'Выйти' }}
        </button>
        <p v-if="sent">
            Письмо успешно отправлено.
        </p>
        <p v-if="errorMessage" role="alert">
            {{ errorMessage }}
        </p>
    </main>
</template>
