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

const resend = async function () {
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
                'Could not send the verification email'
        } else {
        errorMessage.value = 'An unknown error occurred'
        }
    } finally {
        loading.value = false
    }
}

const submitLogout = async function () {
    errorMessage.value = ''
    logoutLoading.value = true
    try {
        await auth.logout()
        await router.push({ name: 'login' })
    } catch {
        errorMessage.value = 'Could not sign out'
    } finally {
        logoutLoading.value = false
    }
}
</script>

<template>
    <AuthLayout
        title="Verify your email"
        description="Follow the link in the email to finish creating your account."
    >
        <p v-if="auth.user.value">
            We sent an email to {{ auth.user.value.email }}.
        </p>
        <BaseButton :loading="loading" loading-text="Sending..." @click="resend">
            Send again
        </BaseButton>
        <BaseButton :loading="logoutLoading" loading-text="Signing out..." @click="submitLogout">
            Sign out
        </BaseButton>
        <AlertMessage
            :message="sent ? 'Verification email sent.' : undefined"
            type="success"
        />
        <AlertMessage :message="errorMessage" />
    </AuthLayout>
</template>
