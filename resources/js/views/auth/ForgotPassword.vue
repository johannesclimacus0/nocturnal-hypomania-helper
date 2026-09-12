<script setup lang="ts">
import { reactive, ref } from 'vue'
import axios from 'axios'
import { forgotPassword } from '../../api/auth'
import AlertMessage from '../../components/AlertMessage.vue'
import FormField from '../../components/FormField.vue'
import SubmitButton from '../../components/SubmitButton.vue'
import AuthLayout from '../../layouts/AuthLayout.vue'

const formData = reactive({
    email: '',
})

const errors = ref<Record<string, string[]>>({})
const message = ref('')
const errorMessage = ref('')
const loading = ref(false)

const submit = async () => {
    errors.value = {}
    message.value = ''
    errorMessage.value = ''
    loading.value = true
    try {
        message.value = await forgotPassword(formData)
    } catch (error: unknown) {
        if (axios.isAxiosError(error)) {
            errors.value = error.response?.data?.errors ?? {}
            errorMessage.value = Object.keys(errors.value).length === 0
                ? error.response?.data?.message ?? 'Could not send the reset email'
                : ''
        } else {
            errorMessage.value = 'An unknown error occurred'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <AuthLayout title="Forgot password?" description="Enter your email and we will send a password reset link.">
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <FormField
                id="email"
                v-model.trim="formData.email"
                label="Email"
                type="email"
                autocomplete="email"
                placeholder="Email"
                :error="errors.email?.[0]"
                required
            />
            <SubmitButton :loading="loading" loading-text="Sending...">
                Send reset link
            </SubmitButton>
            <AlertMessage :message="message" type="success" />
            <AlertMessage :message="errorMessage" />
        </form>

        <template #footer>
            <RouterLink :to="{ name: 'login' }">
                Back to sign in
            </RouterLink>
        </template>
    </AuthLayout>
</template>
