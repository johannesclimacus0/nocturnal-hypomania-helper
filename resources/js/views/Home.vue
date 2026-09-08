<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from "vue-router"
import { useAuth } from '../stores/auth'

const router = useRouter()
const auth = useAuth()
const user = auth.user

const loading = ref(false)
const errorMessage = ref('')

const logout = async function (){
    loading.value = true
    errorMessage.value = ''

    try{
        await auth.logout()
        await router.replace({ name: 'register' })
    } catch {
        errorMessage.value = 'Не удалось выйти с аккаунта'
    } finally {
        loading.value = false
    }
}
</script>

<template>
<main>
    <h2>homepage</h2>
    <section v-if="user">
        <p>Вы вошли как {{ user.name }}</p>
        <p>{{ user.email }}</p>
    </section>
    <button type="button" @click="logout">Выйти</button>
    <p v-if="errorMessage">
        {{ errorMessage }}
    </p>
</main>
</template>

<style scoped>

</style>
