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
interface Entry {
    uuid: string;
    name: string;
    slug?: string;
    is_system?: boolean
}

const entries = ref<Entry[]>([])
const search = ref('')
const loading = ref(true)
const error = ref('')
const name = ref('')
const slug = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const saving = ref(false)
const saveError = ref('')
const saved = ref(false)

const filtered = computed(() => {
    const query = search.value.trim().toLowerCase()
    return entries.value.filter(entry => `${entry.name} ${entry.slug ?? ''} ${entry.uuid}`.toLowerCase().includes(query))
})

const custom = computed(() => filtered.value.filter(entry => !entry.is_system))
const system = computed(() => filtered.value.filter(entry => entry.is_system))

const load = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        entries.value = (await http.get<{ data: Entry[] }>('/api/task-types')).data.data
    } catch {
        error.value = 'Could not load task types.'
    } finally {
        loading.value = false
    }
}

const create = async function (): Promise<void> {
    if (saving.value || !name.value.trim() || !slug.value.trim()) return
    saving.value = true
    fieldErrors.value = {}
    saveError.value = ''
    saved.value = false
    try {
        const response = await http.post<{ data: Entry }>('/api/task-types', {
            name: name.value.trim(),
            slug: slug.value.trim(),
        })
        entries.value.push(response.data.data)
        entries.value.sort((a, b) => a.name.localeCompare(b.name))
        name.value = ''
        slug.value = ''
        search.value = ''
        saved.value = true
    } catch (cause) {
        if (axios.isAxiosError(cause) && cause.response?.status === 422) {
            fieldErrors.value = cause.response.data.errors ?? {}
        } else {
            saveError.value = requestMessage(cause, 'Entry was not saved.')
        }
    } finally {
        saving.value = false
    }
}
onMounted(load)
</script>
<template>
    <PageHeader title="task types" />
    <div class="max-w-4xl space-y-5">
        <BaseInput id="task-types-search" v-model="search" label="search" type="search" placeholder="Search name or UUID..." />
        <p v-if="loading" class="text-xs text-zinc-400">loading...</p>
        <AlertMessage v-else-if="error" :message="error">
            <button type="button" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200" @click="load">
                retry
            </button>
        </AlertMessage>
        <template v-else>
            <Panel title="new task type">
                <form class="space-y-3 px-3 py-3" @submit.prevent="create">
                    <BaseInput id="task-types-name"
                               v-model="name"
                               label="name"
                               required
                               maxlength="100"
                               :disabled="saving"
                               :error="fieldErrors.name?.[0]"
                    />
                    <BaseInput id="task-types-slug"
                               v-model="slug"
                               label="slug"
                               placeholder="e.g. daily-review"
                               required
                               maxlength="100"
                               pattern="[a-z0-9]+(-[a-z0-9]+)*"
                               :disabled="saving"
                               :error="fieldErrors.slug?.[0]"
                    />
                    <AlertMessage :message="saveError" />
                    <BaseButton type="submit"
                                :loading="saving"
                                -text="adding..."
                                :disabled="saving || !name.trim() || !slug.trim()"
                    >+ add
                    </BaseButton>
                    <p v-if="saved" class="text-xs text-lime-200">entry added</p>
                </form>
            </Panel>
            <Panel title="custom entries">
                <EmptyState v-if="!custom.length" :message="search ? 'no matches' : 'no custom types yet'" />
                <div v-for="entry in custom" :key="entry.uuid" class="group flex min-w-0 items-baseline gap-4 border-b border-zinc-800/40 px-3 py-2 text-xs leading-5 last:border-b-0 hover:bg-zinc-900/50">
                    <div class="flex min-w-0 flex-1 items-baseline gap-2">
                        <span class="truncate text-zinc-300 group-hover:text-zinc-100"
                              :title="entry.name"
                        >{{ entry.name }}
                        </span>
                        <span v-if="entry.slug && entry.slug !== entry.name"
                              class="hidden min-w-0 truncate text-[0.625rem] text-zinc-500 sm:inline"
                              :title="entry.slug"
                        >/ {{ entry.slug }}
                        </span>
                    </div>
                    <span class="shrink-0 font-mono text-[0.625rem] tabular-nums text-zinc-500" :title="entry.uuid">
                        <span class="text-zinc-600">#</span>{{ shortUuid(entry.uuid) }}
                    </span>
                </div>
            </Panel>
            <Panel title="system types">
                <EmptyState v-if="!system.length" message="no system types found" />
                <div v-for="entry in system" :key="entry.uuid" class="border-b border-zinc-800/40 px-3 py-2 text-xs leading-5 text-zinc-400 last:border-b-0">
                    {{ entry.slug }}
                </div>
            </Panel>
        </template>
    </div>
</template>
