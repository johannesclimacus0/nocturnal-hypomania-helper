<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { login } from '../api/auth'
import { useAuth } from '../stores/auth'

const router = useRouter()
const auth = useAuth()

const form = reactive({
    email: '',
    password: '',
})

const errors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const loading = ref(false)

const submit = async function () {
    errors.value = {}
    errorMessage.value = ''
    loading.value = true
    try {
        await login({ ...form })
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
    <main>
        <h2>Вход</h2>
        <form @submit.prevent="submit">
            <div>
                <label for="email">Email</label>
                <input
                    id="email"
                    v-model.trim="form.email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                >
                <p v-if="errors.email" role="alert">
                    {{ errors.email[0] }}
                </p>
            </div>
            <div>
                <label for="password">Пароль</label>
                <input
                    id="password"
                    v-model="form.password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                >
                <p v-if="errors.password" role="alert">
                    {{ errors.password[0] }}
                </p>
            </div>
            <button type="submit" :disabled="loading">
                {{ loading ? 'Входим...' : 'Войти' }}
            </button>
            <p v-if="errorMessage" role="alert">
                {{ errorMessage }}
            </p>
        </form>
        <nav>
            <RouterLink :to="{ name: 'forgot-password' }">
                Забыли пароль?
            </RouterLink>
            <RouterLink :to="{ name: 'register' }">
                Создать аккаунт
            </RouterLink>
        </nav>
    </main>
</template>
