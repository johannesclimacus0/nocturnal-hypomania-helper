<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { register } from '../../api/auth'
import { useAuth } from '../../stores/auth'
import AlertMessage from '../../components/AlertMessage.vue'
import FormField from '../../components/FormField.vue'
import SubmitButton from '../../components/SubmitButton.vue'
import AuthLayout from '../../layouts/AuthLayout.vue'

const router = useRouter()
const auth = useAuth()

const formData = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const errors = ref<Record<string, string[]>>({})
const generalError = ref('')
const loading = ref(false)

const submit = async function () {
    errors.value = {}
    generalError.value = ''
    loading.value = true

    try {
        await register({ ...formData })
        await auth.refresh()

        if (!auth.user.value) {
            throw new Error('Account created, but no session was found')
        }

        await router.push({
            name: auth.user.value.email_verified_at ? 'home' : 'verify-email',
        })
    } catch (error: unknown) {
        if (axios.isAxiosError(error)) {
            errors.value = error.response?.data?.errors ?? {}

            if (Object.keys(errors.value).length === 0) {
                generalError.value =
                    error.response?.data?.message ??
                    'Could not create your account'
            }
        } else {
            generalError.value =
                error instanceof Error
                    ? error.message
                    : 'An unknown error occurred'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <AuthLayout title="Create an account" description="Create an account to continue.">
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <FormField
                id="name"
                v-model.trim="formData.name"
                label="Name"
                type="text"
                autocomplete="name"
                :error="errors.name?.[0]"
                required
            />
            <FormField
                id="email"
                v-model.trim="formData.email"
                label="Email"
                type="email"
                autocomplete="email"
                :error="errors.email?.[0]"
                required
            />
            <FormField
                id="password"
                v-model="formData.password"
                label="Password"
                type="password"
                autocomplete="new-password"
                minlength="8"
                maxlength="64"
                :error="errors.password?.[0]"
                required
            />
            <FormField
                id="password_confirmation"
                v-model="formData.password_confirmation"
                label="Confirm password"
                type="password"
                autocomplete="new-password"
                minlength="8"
                maxlength="64"
                :error="errors.password_confirmation?.[0]"
                required
            />
            <SubmitButton :loading="loading" loading-text="Creating account...">
                Create account
            </SubmitButton>
            <AlertMessage :message="generalError" />
        </form>

        <template #footer>
            <RouterLink :to="{ name: 'login' }">
                Already have an account? Sign in
            </RouterLink>
        </template>
    </AuthLayout>
</template>
