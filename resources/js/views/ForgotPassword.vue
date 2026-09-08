<script setup lang="ts">
import { reactive, ref } from 'vue'
import axios from 'axios'
import { forgotPassword } from '../api/auth'

const formData = reactive({
    email: '',
})

const errors = ref<Record<string, string[]>>({})
const message = ref('')
const loading = ref(false)

const submit = async ()=>{
    errors.value = {}
    message.value = ''
    loading.value = true
    try {
        message.value = await forgotPassword(formData)
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
        <h2>Забыли пароль?</h2>
        <form @submit.prevent="submit">
            <div>
                <label>Email</label>
                <input v-model="formData.email" type="email" placeholder="Email">
                <p v-if="errors.email">
                    {{ errors.email[0] }}
                </p>
            </div>
            <button type="submit" :disabled="loading">
                {{ loading ? 'Отправляем...' : 'Выслать подтверждение' }}
            </button>

            <p v-if="message">
                {{ message }}
            </p>
        </form>
    </main>
</template>
