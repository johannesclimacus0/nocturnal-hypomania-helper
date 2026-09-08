<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { resetPassword } from '../api/auth'

const route = useRoute()
const router = useRouter()

const formData = reactive({
    email: String(route.query.email ?? ''),
    password: '',
    password_confirmation: '',
})

const errors = ref<Record<string, string[]>>({})
const loading = ref(false)

const submit = async () => {
    errors.value = {}
    loading.value = true
    try {
        await resetPassword({
            token: String(route.params.token),
            email: formData.email,
            password: formData.password,
            password_confirmation: formData.password_confirmation,
        })
        await router.push({
            name: 'register',
        })
    } catch (error) {
        if (axios.isAxiosError(error)) {
            errors.value = error.response.data.errors
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <main>
        <h2>Изменить пароль</h2>
        <form @submit.prevent="submit">
            <div>
                <label>Email</label>
                <input v-model="formData.email" type="email">
                <span v-if="errors.email">
                    {{ errors.email[0] }}
                </span>
            </div>
            <div>
                <label>Новой пароль</label>
                <input v-model="formData.password" type="password">
                <span v-if="errors.password">
                    {{ errors.password[0] }}
                </span>
            </div>
            <div>
                <label>Повторите пароль</label>
                <input v-model="formData.password_confirmation" type="password">
            </div>
            <button type="submit" :disabled="loading">
                {{ loading ? 'Меняем пароль...' : 'Сменить пароль' }}
            </button>
        </form>
    </main>
</template>
