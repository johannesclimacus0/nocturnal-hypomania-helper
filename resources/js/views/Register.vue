<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { register } from '../api/auth'
import { useAuth } from '../stores/auth'

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

async function submit() {
    errors.value = {}
    generalError.value = ''
    loading.value = true

    try {
        await register({ ...formData })
        await auth.refresh()

        if (!auth.user.value) {
            throw new Error('Пользователь создан, но сессия не найдена')
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
                    'Не удалось зарегистрироваться'
            }
        } else {
            generalError.value =
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
        <h1>Регистрация</h1>
        <form @submit.prevent="submit">
            <div>
                <label for="name">Имя</label>
                <input
                    id="name"
                    v-model.trim="formData.name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    required
                >

                <p v-if="errors.name" role="alert">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div>
                <label for="email">Email</label>
                <input
                    id="email"
                    v-model.trim="formData.email"
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
                    v-model="formData.password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                <p v-if="errors.password" role="alert">
                    {{ errors.password[0] }}
                </p>
            </div>
            <div>
                <label for="password_confirmation">
                    Повторите пароль
                </label>
                <input
                    id="password_confirmation"
                    v-model="formData.password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                <p v-if="errors.password_confirmation" role="alert">
                    {{ errors.password_confirmation[0] }}
                </p>
            </div>
            <button type="submit" :disabled="loading">
                {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
            </button>
            <p v-if="generalError" role="alert">
                {{ generalError }}
            </p>
        </form>
        <nav>
            <RouterLink :to="{ name: 'forgot-password' }">
                Забыли пароль?
            </RouterLink>
            <RouterLink :to="{ name: 'login' }">
                Войти
            </RouterLink>
        </nav>
    </main>
</template>
