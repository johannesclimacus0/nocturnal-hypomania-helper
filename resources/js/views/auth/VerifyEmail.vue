<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { resendVerificationEmail } from '../../api/auth'
import { useAuth } from '../../stores/auth'
import AlertMessage from '../../components/AlertMessage.vue'
import BaseButton from '../../components/BaseButton.vue'
import AuthLayout from '../../layouts/AuthLayout.vue'

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
        await router.push({ name: 'login' })
    } catch {
        errorMessage.value = 'Не удалось выйти из аккаунта'
    } finally {
        logoutLoading.value = false
    }
}
</script>

<template>
    <AuthLayout
        title="Подтвердите почту"
        description="Перейдите по ссылке в письме, чтобы завершить регистрацию."
    >
        <p v-if="auth.user.value">
            Письмо отправлено на почту: {{ auth.user.value.email }}.
        </p>
        <BaseButton :loading="loading" loading-text="Отправляем..." @click="resend">
            Отправить снова
        </BaseButton>
        <BaseButton :loading="logoutLoading" loading-text="Выходим..." @click="submitLogout">
            Выйти
        </BaseButton>
        <AlertMessage
            :message="sent ? 'Письмо успешно отправлено.' : undefined"
            type="success"
        />
        <AlertMessage :message="errorMessage" />
    </AuthLayout>
</template>
