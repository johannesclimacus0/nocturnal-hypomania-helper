<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseButton from '../../components/BaseButton.vue'
import StatusDot from '../../components/StatusDot.vue'
import { formatDate, shortUuid } from '../../utils/display'
import { deleteTask, getTask, type Task } from '../../api/tasks'

const route = useRoute()
const router = useRouter()
const task = ref<Task | null>(null)
const loading = ref(true)
const deleting = ref(false)
const error = ref('')

const taskUuid = computed(() => String(route.params.uuid ?? ''))

const loadTask = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        task.value = await getTask(taskUuid.value)
    } catch (requestError) {
        error.value = axios.isAxiosError(requestError) && requestError.response?.status === 404
            ? 'Task not found'
            : 'Could not load this task.'
    } finally {
        loading.value = false
    }
}

const archiveTask = async function (): Promise<void> {
    if (!task.value || deleting.value) return
    deleting.value = true
    error.value = ''
    try {
        await deleteTask(task.value.uuid)
        await router.push('/tasks')
    } catch {
        error.value = 'Task was not archived.'
    } finally {
        deleting.value = false
    }
}

onMounted(loadTask)
</script>

<template>
    <PageHeader :title="task?.title ?? 'task'" />
    <p v-if="loading" class="text-xs text-zinc-400">loading...</p>
    <AlertMessage v-if="error" :message="error" class="mb-4" />
    <template v-if="!loading && task">
        <div class="mb-6 flex flex-wrap items-center gap-4 border-b border-zinc-800 pb-5">
            <StatusDot :tone="task.status === 'active' ? 'accent' : 'muted'">
                {{ task.status }}
            </StatusDot>
            <span class="font-mono text-[0.625rem] text-zinc-500" :title="taskUuid">
                #{{ taskUuid }}
            </span>
        </div>
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_11.25rem]">
            <div>
                <Panel title="description">
                    <div class="whitespace-pre-wrap break-words px-3 py-3 text-xs leading-6 text-zinc-300">
                        {{ task.description || '—' }}
                    </div>
                </Panel>
                <div class="mt-6">
                    <h2 class="mb-3 text-xs text-zinc-400">activity</h2>
                    <p class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-3 py-1.5 text-xs">
                        <time class="text-zinc-400">created</time>
                        <span>{{ formatDate(task.created_at) }}</span>
                    </p>
                    <p class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-3 py-1.5 text-xs">
                        <time class="text-zinc-400">updated</time>
                        <span>{{ formatDate(task.updated_at) }}</span>
                    </p>
                </div>
            </div>
            <aside class="border-t border-zinc-800 pt-4 xl:border-t-0 xl:border-l xl:pl-5 xl:pt-0"><dl class="space-y-4 break-words text-xs [&_dt]:mb-1 [&_dt]:text-zinc-500"><div><dt>area</dt><dd>{{ task.area_name || 'none' }}</dd></div><div><dt>category</dt><dd>{{ task.category_name || 'none' }}</dd></div><div><dt>type</dt><dd>{{ task.task_type_name }}</dd></div><div><dt>difficulty</dt><dd class="text-zinc-300">{{ task.difficulty }}</dd></div><div><dt>estimate</dt><dd>{{ task.estimated_time_minutes }} minutes</dd></div><div><dt>last completed</dt><dd class="text-zinc-400">{{ formatDate(task.last_completed_at) }}</dd></div></dl></aside>
        </div>
        <div class="mt-6 flex flex-wrap gap-3 border-t border-zinc-800 pt-3">
            <RouterLink :to="`/tasks/${task.uuid}/edit`"
                class="inline-flex min-h-8 items-center justify-center gap-2 border border-zinc-700 px-3 py-1 text-xs hover:bg-zinc-800"
            >edit task
            </RouterLink>
            <BaseButton :loading="deleting"
                        loading-text="archiving..."
                        class="text-red-400"
                        @click="archiveTask"
            >archive
            </BaseButton>
        </div>
    </template>
</template>
