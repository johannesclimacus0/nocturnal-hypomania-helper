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

        if (result.two_factor) {
            throw new Error('Двухфакторная аутентификация временно недоступна')
        }

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
            throw new Error('Не удалось получить пользователя после входа')
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
                    'Не удалось войти в аккаунт'
            }
        } else {
            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'Произошла неизвестная ошибка'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <AuthLayout title="Вход" description="Войдите в свой аккаунт.">
        <form @submit.prevent="submit">
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
                label="Пароль"
                type="password"
                autocomplete="current-password"
                :error="errors.password?.[0]"
                required
            />
            <SubmitButton :loading="loading" loading-text="Входим...">
                Войти
            </SubmitButton>
            <label>
                <input v-model="form.remember" type="checkbox">
                Запомнить меня
            </label>
            <AlertMessage
                :message="$route.query.reset === 'success' ? 'Пароль изменён. Теперь можно войти.' : undefined"
                type="success"
            />
            <AlertMessage :message="errorMessage" />
        </form>

        <template #footer>
            <RouterLink :to="{ name: 'forgot-password' }">
                Забыли пароль?
            </RouterLink>
            <RouterLink :to="{ name: 'register' }">
                Создать аккаунт
            </RouterLink>
        </template>
    </AuthLayout>
</template>
