<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { resetPassword } from '../../api/auth'
import AlertMessage from '../../components/AlertMessage.vue'
import FormField from '../../components/FormField.vue'
import SubmitButton from '../../components/SubmitButton.vue'
import AuthLayout from '../../layouts/AuthLayout.vue'

const route = useRoute()
const router = useRouter()

const formData = reactive({
    email: String(route.query.email ?? ''),
    password: '',
    password_confirmation: '',
})

const errors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const loading = ref(false)

const submit = async () => {
    errors.value = {}
    errorMessage.value = ''
    loading.value = true
    try {
        await resetPassword({
            token: String(route.params.token),
            email: formData.email,
            password: formData.password,
            password_confirmation: formData.password_confirmation,
        })
        await router.replace({
            name: 'login',
            query: { reset: 'success' },
        })
    } catch (error: unknown) {
        if (axios.isAxiosError(error)) {
            errors.value = error.response?.data?.errors ?? {}
            errorMessage.value = Object.keys(errors.value).length === 0
                ? error.response?.data?.message ?? 'Не удалось изменить пароль'
                : ''
        } else {
            errorMessage.value = 'Произошла неизвестная ошибка'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <AuthLayout title="Изменить пароль" description="Введите новый пароль для аккаунта.">
        <form @submit.prevent="submit">
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
                label="Новый пароль"
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
                label="Повторите пароль"
                type="password"
                autocomplete="new-password"
                minlength="8"
                maxlength="64"
                :error="errors.password_confirmation?.[0]"
                required
            />
            <SubmitButton :loading="loading" loading-text="Меняем пароль...">
                Сменить пароль
            </SubmitButton>
        </form>
        <AlertMessage :message="errorMessage" />

        <template #footer>
            <RouterLink :to="{ name: 'login' }">
                Вернуться ко входу
            </RouterLink>
        </template>
    </AuthLayout>
</template>
