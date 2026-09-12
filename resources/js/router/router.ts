import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from 'vue-router'
import { useAuth } from "../stores/auth";

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        component: () => import('../views/auth/Home.vue'),
        meta: { requiresAuth: true, requiresVerifiedEmail: true },
        children: [
            { path: '', name: 'home', component: () => import('../views/app/Tonight.vue'), meta: { label: 'tonight' } },
            { path: 'tasks', component: () => import('../views/app/TasksIndex.vue'), meta: { label: 'tasks' } },
            { path: 'tasks/create', component: () => import('../views/app/TaskForm.vue'), meta: { label: 'tasks / new' } },
            { path: 'tasks/:uuid', component: () => import('../views/app/TaskDetail.vue'), meta: { label: 'tasks / detail' } },
            { path: 'tasks/:uuid/edit', component: () => import('../views/app/TaskForm.vue'), props: { editing: true }, meta: { label: 'tasks / edit' } },
            { path: 'sessions', component: () => import('../views/app/SessionsIndex.vue'), meta: { label: 'sessions' } },
            { path: 'sessions/:uuid', component: () => import('../views/app/SessionDetail.vue'), meta: { label: 'sessions / run' } },
            { path: 'areas', component: () => import('../views/app/AreasIndex.vue'), meta: { label: 'taxonomy / areas' } },
            { path: 'categories', component: () => import('../views/app/CategoriesIndex.vue'), meta: { label: 'taxonomy / categories' } },
            { path: 'task-types', component: () => import('../views/app/TaskTypesIndex.vue'), meta: { label: 'taxonomy / types' } },
        ],
    },
    { path: '/register', name: 'register', component: () => import('../views/auth/Register.vue'), meta: { guestOnly: true } },
    { path: '/verify-email', name: 'verify-email', component: () => import('../views/auth/VerifyEmail.vue'), meta: { requiresAuth: true, requiresUnverifiedEmail: true } },
    { path: '/forgot-password', name: 'forgot-password', component: () => import('../views/auth/ForgotPassword.vue'), meta: { guestOnly: true } },
    { path: '/reset-password/:token', name: 'reset-password', component: () => import('../views/auth/ResetPassword.vue'), meta: { guestOnly: true } },
    { path: '/home', redirect: { name: 'home' } },
    { path: '/login', name: 'login', component: ()=> import('../views/auth/Login.vue'), meta: { guestOnly: true }}
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const auth = useAuth()

    await auth.initialize()

    if (auth.unavailable.value) {
        return false
    }

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
