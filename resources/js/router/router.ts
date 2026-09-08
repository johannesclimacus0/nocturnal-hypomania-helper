import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from 'vue-router'
import { useAuth } from "../stores/auth";

const routes: RouteRecordRaw[] = [
    { path: '/', redirect: { name: 'register' } },
    { path: '/register', name: 'register', component: () => import('../views/Register.vue'), meta: { guestOnly: true } },
    { path: '/verify-email', name: 'verify-email', component: () => import('../views/VerifyEmail.vue'), meta: { requiresAuth: true, requiresUnverifiedEmail: true } },
    { path: '/forgot-password', name: 'forgot-password', component: () => import('../views/ForgotPassword.vue'), meta: { guestOnly: true } },
    { path: '/reset-password/:token', name: 'reset-password', component: () => import('../views/ResetPassword.vue'), meta: { guestOnly: true } },
    { path: '/home', name: 'home', component: () => import('../views/Home.vue'), meta: { requiresAuth: true, requiresVerifiedEmail: true} },
    { path: '/login', name: 'login', component: ()=> import('../views/Login.vue'), meta: { guestOnly: true }}
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const auth = useAuth()

    await auth.initialize()

    if (to.meta.requiresUnverifiedEmail) {
        await auth.refresh()
    }

    const user = auth.user.value

    if (to.meta.requiresAuth && !user) {
        return { name: 'login' }
    }

    if (to.meta.guestOnly && user) {
        return user.email_verified_at
            ? { name: 'home' }
            : { name: 'verify-email' }
    }

    if (to.meta.requiresUnverifiedEmail && user?.email_verified_at) {
        return { name: 'home' }
    }

    if (to.meta.requiresVerifiedEmail && user && !user.email_verified_at) {
        return { name: 'verify-email' }
    }

    return true
})

export default router
