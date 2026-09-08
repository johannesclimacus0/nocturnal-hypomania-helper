import { ref } from 'vue'
import {
    getCurrentUser,
    logout as sendLogoutRequest,
    type User,
} from '../api/auth'

const user = ref<User | null>(null)
const initialized = ref(false)

async function refresh(): Promise<void> {
    try {
        user.value = await getCurrentUser()
    } catch {
        user.value = null
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
        initialize,
        refresh,
        logout,
    }
}
