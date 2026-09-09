import { ref } from 'vue'
import axios from 'axios'
import {
    getCurrentUser,
    logout as sendLogoutRequest,
    type User,
} from '../api/auth'

const user = ref<User | null>(null)
const initialized = ref(false)
const unavailable = ref(false)

async function refresh(): Promise<void> {
    try {
        user.value = await getCurrentUser()
        unavailable.value = false
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && [401, 419].includes(error.response?.status ?? 0)) {
            user.value = null
            unavailable.value = false
            return
        }

        unavailable.value = true
    } finally {
        initialized.value = true
    }
}

async function initialize(): Promise<void> {
    if (initialized.value) {
        return
    }

    await refresh()
}

async function logout(): Promise<void> {
    await sendLogoutRequest()
    user.value = null
}

export function useAuth() {
    return {
        user,
        initialized,
        unavailable,
        initialize,
        refresh,
        logout,
    }
}
