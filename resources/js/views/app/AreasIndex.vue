<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import EmptyState from '../../components/EmptyState.vue'
import http from '../../api/http'
import { requestMessage, shortUuid } from '../../utils/display'

interface Entry { uuid: string; name: string }

const entries = ref<Entry[]>([])
const search = ref('')
const loading = ref(true)
const error = ref('')
const name = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const saving = ref(false)
const saveError = ref('')
const saved = ref(false)

const filtered = computed(() => {
    const query = search.value.trim().toLowerCase()
    return entries.value.filter(entry => `${entry.name} ${entry.uuid}`.toLowerCase().includes(query))
})

const load = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        const response = await http.get<{ data: Entry[] }>('/api/areas')
        entries.value = response.data.data
    } catch {
        error.value = 'Could not load areas. Retry the request.'
    } finally {
        loading.value = false
    }
}

const create = async function (): Promise<void> {
    if (saving.value || !name.value.trim()) return
    saving.value = true
    fieldErrors.value = {}
    saveError.value = ''
    saved.value = false
    try {
        const response = await http.post<{ data: Entry }>('/api/areas', {
            name: name.value.trim(),
        })
        entries.value.push(response.data.data)
        entries.value.sort((a, b) => a.name.localeCompare(b.name))
        name.value = ''
        search.value = ''
        saved.value = true
    } catch (cause) {
        if (axios.isAxiosError(cause) && cause.response?.status === 422) {
            fieldErrors.value = cause.response.data.errors ?? {}
        } else {
            saveError.value = requestMessage(cause, 'Entry was not saved. Check your connection and try again.')
        }
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>
<template>
    <PageHeader title="areas" />
    <div class="max-w-4xl space-y-5">
        <BaseInput id="areas-search"
                   v-model="search"
                   label="search"
                   type="search"
                   placeholder="Search name or UUID..."
        />
        <p v-if="loading" class="text-xs text-zinc-400">loading...</p>
        <AlertMessage v-else-if="error" :message="error">
            <button type="button" @click="load" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200">
                retry
            </button>
        </AlertMessage>
        <template v-else>
            <Panel title="new area">
                <form class="space-y-3 px-3 py-3" @submit.prevent="create">
                    <BaseInput id="areas-name"
                               v-model="name"
                               label="name"
                               required
                               maxlength="100"
                               :disabled="saving"
                               :error="fieldErrors.name?.[0]"
                    />
                    <AlertMessage :message="saveError" />
                    <BaseButton type="submit"
                                :loading="saving"
                                loading-text="adding..."
                                :disabled="saving || !name.trim()"
                    >
                        + add
                    </BaseButton>
                    <p v-if="saved" class="text-xs text-lime-200">entry added</p>
                </form>
            </Panel>
            <Panel title="custom entries">
                <EmptyState v-if="!filtered.length" :message="search ? 'no matches' : 'no areas yet'" />
                <div v-for="entry in filtered" :key="entry.uuid" class="group flex min-w-0 items-baseline gap-4 border-b border-zinc-800/40 px-3 py-2 text-xs leading-5 last:border-b-0 hover:bg-zinc-900/50">
                    <div class="flex min-w-0 flex-1 items-baseline gap-2">
                        <span class="truncate text-zinc-300 group-hover:text-zinc-100" :title="entry.name">
                            {{ entry.name }}
                        </span>
                    </div>
                    <span class="shrink-0 font-mono text-[0.625rem] tabular-nums text-zinc-500" :title="entry.uuid">
                        <span class="text-zinc-600">#</span>{{ shortUuid(entry.uuid) }}
                    </span>
                </div>
            </Panel>
        </template>
    </div>
</template>
