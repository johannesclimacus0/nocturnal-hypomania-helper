<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseButton from '../../components/BaseButton.vue'
import SessionsTable from '../../components/SessionsTable.vue'
import { shortUuid } from '../../utils/display'
import { createSession, getSessions, type Session } from '../../api/sessions'
const router = useRouter()
const sessions = ref<Session[]>([])
const loading = ref(true)
const starting = ref(false)
const error = ref('')
const startError = ref('')

const active = computed(() => sessions.value.filter(session => !session.ended_at))

const load = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try { sessions.value = await getSessions() }
    catch { error.value = 'Could not load sessions.' }
    finally { loading.value = false }
}

const start = async function (): Promise<void> {
    if (starting.value) return

    starting.value = true
    startError.value = ''
    try {
        const session = await createSession()
        sessions.value.unshift(session)
        await router.push(`/sessions/${session.uuid}`)
    } catch {
        startError.value = 'Session could not be started.'
    }
    finally {
        starting.value = false
    }
}
onMounted(load)
</script>
<template>
    <PageHeader title="tonight" />
    <p v-if="loading" class="text-xs text-zinc-400">loading sessions...</p>
    <AlertMessage v-else-if="error" :message="error">
        <button type="button" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200" @click="load">
            retry
        </button>
    </AlertMessage>
    <div v-else class="grid gap-6 lg:grid-cols-[minmax(0,0.65fr)_minmax(0,1.35fr)]">
        <section class="min-w-0 lg:border-r lg:border-zinc-700 lg:pr-5">
            <template v-if="active.length">
                <p class="mb-4 text-xs text-zinc-400">Continue an active session</p>
                <RouterLink v-for="session in active"
                            :key="session.uuid"
                            :to="`/sessions/${session.uuid}`"
                            class="mb-3 block break-all text-xs text-lime-200"
                >resume session #{{ shortUuid(session.uuid) }}
                </RouterLink>
            </template>
            <template v-else>
                <p class="mb-5 text-xs text-zinc-400">Start a session, then choose tasks to work on.</p>
                <BaseButton :loading="starting"
                            loading-text="starting..."
                            class="text-lime-200" @click="start">start session
                </BaseButton>
            </template>
            <AlertMessage :message="startError" class="mt-3" />
        </section>
        <section class="min-w-0">
            <Panel title="recent sessions">
                <SessionsTable :sessions="sessions.slice(0, 5)" />
            </Panel>
            <RouterLink to="/sessions" class="mt-3 inline-block text-xs text-lime-200">
                all sessions
            </RouterLink>
        </section>
    </div>
</template>
