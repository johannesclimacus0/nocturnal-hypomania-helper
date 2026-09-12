<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { onMounted, ref } from 'vue'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import SessionsTable from '../../components/SessionsTable.vue'
import BaseButton from '../../components/BaseButton.vue'
import { getSessions, type Session } from '../../api/sessions'
const sessions = ref<Session[]>([])
const loading = ref(true)
const error = ref('')

const load = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try { sessions.value = await getSessions() }
    catch { error.value = 'Could not load sessions. Retry the request.' }
    finally { loading.value = false }
}

onMounted(load)
</script>
<template>
    <PageHeader title="sessions" />
    <p v-if="loading" class="py-4 text-xs text-zinc-400">loading sessions...</p>
    <AlertMessage v-else-if="error" :message="error">
        <button type="button" @click="load" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200">
            retry
        </button>
    </AlertMessage>
    <Panel v-else title="recent runs">
        <template #actions>
            <span class="text-xs text-zinc-400">{{ sessions.length }} runs</span>
        </template>
        <SessionsTable :sessions="sessions" />
    </Panel>
    <RouterLink to="/" class="mt-4 inline-block text-xs text-lime-200">tonight</RouterLink>
</template>
