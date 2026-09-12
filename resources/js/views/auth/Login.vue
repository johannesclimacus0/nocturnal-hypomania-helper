<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { login } from '../../api/auth'
import { useAuth } from '../../stores/auth'
import AlertMessage from '../../components/AlertMessage.vue'
import FormField from '../../components/FormField.vue'
import SubmitButton from '../../components/SubmitButton.vue'
import AuthLayout from '../../layouts/AuthLayout.vue'

const router = useRouter()
const auth = useAuth()

const form = reactive({
    email: '',
    password: '',
    remember: false,
})

const errors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const loading = ref(false)

const submit = async function () {
    errors.value = {}
    errorMessage.value = ''
    loading.value = true
    try {
        const result = await login({ ...form })

        if (result.redirect) {
            const target = new URL(result.redirect, window.location.origin)

            if (target.origin === window.location.origin && target.pathname.startsWith('/email/verify/')) {
                window.location.assign(target.href)
                return
            }
        }

        await auth.refresh()

        const user = auth.user.value

        if (!user) {
            throw new Error('Could not load your account after signing in')
        }
        await router.replace({
            name: user.email_verified_at ? 'home' : 'verify-email',
        })
    } catch (error) {
        if (axios.isAxiosError(error)) {
            errors.value = error.response?.data?.errors ?? {}

            if (Object.keys(errors.value).length === 0) {
                errorMessage.value =
                    error.response?.data?.message ??
                    'Could not sign in'
            }
        } else {
            errorMessage.value =
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
    <AuthLayout title="Sign in" description="Sign in to your account.">
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <FormField
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                autocomplete="email"
                :error="errors.email?.[0]"
                required
            />
            <FormField
                id="password"
                v-model="form.password"
                label="Password"
                type="password"
                autocomplete="current-password"
                :error="errors.password?.[0]"
                required
            />
            <SubmitButton :loading="loading" loading-text="Signing in...">
                Sign in
            </SubmitButton>
            <label>
                <input v-model="form.remember" type="checkbox" class="accent-lime-200">
                Remember me
            </label>
            <AlertMessage
                :message="$route.query.reset === 'success' ? 'Password changed. You can now sign in.' : undefined"
                type="success"
            />
            <AlertMessage :message="errorMessage" />
        </form>

        <template #footer>
            <RouterLink :to="{ name: 'forgot-password' }">
                Forgot password?
            </RouterLink>
            <RouterLink :to="{ name: 'register' }">
                Create an account
            </RouterLink>
        </template>
    </AuthLayout>
</template>
