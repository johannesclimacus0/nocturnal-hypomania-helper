<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, ref } from 'vue'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import EmptyState from '../../components/EmptyState.vue'
import StatusDot from '../../components/StatusDot.vue'
import { shortUuid } from '../../utils/display'
import { getTasks, type Task, type TaskStatus } from '../../api/tasks'

const tasks = ref<Task[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const status = ref<TaskStatus | ''>('')
const area = ref('')
const selectedTaskUuid = ref<string | null>(null)

const areas = computed(() => {
    const options = new Map<string, string>()
    for (const task of tasks.value) {
        if (task.area_uuid) options.set(task.area_uuid, task.area_name ?? 'Unnamed area')
    }
    return Array.from(options, ([uuid, name]) => ({ uuid, name }))
        .sort((a, b) => a.name.localeCompare(b.name))
})

const filteredTasks = computed(() => {
    const query = search.value.trim().toLowerCase()
    return tasks.value.filter(task =>
        (!query || task.title.toLowerCase().includes(query) || task.uuid.toLowerCase().includes(query)) &&
        (!status.value || task.status === status.value) &&
        (!area.value || (area.value === 'none' ? !task.area_uuid : task.area_uuid === area.value)),
    )
})

const counts = computed(() => {
    const result = { active: 0, completed: 0, archived: 0 }
    for (const task of filteredTasks.value) result[task.status]++
    return result
})

const dateFormat = new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short' })

const formatDate = function (value: string | null): string {
    return value ? dateFormat.format(new Date(value)) : '—'
}

const loadTasks = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        tasks.value = await getTasks()
    } catch {
        error.value = 'Could not load tasks.'
    } finally {
        loading.value = false
    }
}
onMounted(loadTasks)
</script>

<template>
    <PageHeader title="tasks" />
    <section class="min-w-0">
        <div class="mb-4 grid grid-cols-[repeat(auto-fit,minmax(min(100%,10rem),1fr))] gap-3">
            <BaseInput
                id="task-search"
                v-model="search"
                label="search"
                type="search"
                placeholder="Search title or UUID..."
            />
            <BaseSelect id="task-status" v-model="status" label="status">
                <option value="">all statuses</option>
                <option value="active">active</option>
                <option value="completed">completed</option>
                <option value="archived">archived</option>
            </BaseSelect>
            <BaseSelect id="task-area" v-model="area" label="area">
                <option value="">all areas</option>
                <option value="none">no area</option>
                <option v-for="option in areas" :key="option.uuid" :value="option.uuid">{{ option.name }}</option>
            </BaseSelect>
        </div>
        <Panel>
            <p v-if="loading" class="px-4 py-6 text-xs text-zinc-400">loading tasks...</p>
            <AlertMessage v-else-if="error" :message="error"><button type="button" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200" @click="loadTasks">retry</button></AlertMessage>
            <template v-else>
                <div class="flex flex-wrap items-center gap-5 border-b border-zinc-800 bg-zinc-900 px-4 py-3 text-xs">
                    <span class="text-lime-200">{{ counts.active }} open</span>
                    <span class="text-zinc-400">{{ counts.completed }} completed</span>
                    <span class="text-zinc-400">{{ counts.archived }} archived</span>
                    <span class="ml-auto text-[0.625rem] text-zinc-400">{{ filteredTasks.length }} of {{ tasks.length }} tasks</span>
                </div>
                <EmptyState v-if="!tasks.length" message="no tasks yet" />
                <EmptyState v-else-if="!filteredTasks.length" message="no matching tasks" />
                <div v-for="task in filteredTasks" :key="task.uuid"
                    class="flex cursor-pointer flex-wrap items-center gap-x-4 gap-y-2 border-b border-zinc-800 px-3 py-2.5 last:border-b-0"
                    :class="selectedTaskUuid === task.uuid ? 'bg-zinc-900' : ''"
                    @click="selectedTaskUuid = task.uuid"
                >
                    <StatusDot :tone="task.status === 'active' ? 'accent' : task.status === 'completed' ? 'info' : 'muted'">
                        <span class="sr-only">{{ task.status }}</span>
                    </StatusDot>
                    <div class="min-w-0 flex-1">
                        <div class="flex min-w-0 flex-wrap items-baseline gap-x-2 gap-y-1">
                            <RouterLink :to="`/tasks/${task.uuid}`"
                                class="box-decoration-clone break-words px-1 py-0.5 hover:text-lime-200"
                                :class="selectedTaskUuid === task.uuid ? 'bg-zinc-300 text-zinc-950 hover:text-zinc-950' : 'text-zinc-300'"
                                @click.stop
                            >
                                {{ task.title }}
                            </RouterLink>
                            <span class="font-mono text-[0.625rem] leading-5 tabular-nums text-zinc-500" :title="task.uuid">
                                #{{ shortUuid(task.uuid, 16) }}
                            </span>
                        </div>
                        <div v-if="selectedTaskUuid === task.uuid" class="mt-1 text-[0.625rem] leading-5">
                            <div class="flex flex-wrap items-baseline gap-x-4 text-zinc-500">
                                <time :datetime="task.created_at ?? undefined">{{ formatDate(task.created_at) }}</time>
                                <span>{{ task.status }}</span>
                            </div>
                            <dl class="flex flex-wrap gap-x-4 text-zinc-400">
                                <div class="flex gap-1"><dt class="text-zinc-500">area:</dt><dd>{{ task.area_name ?? 'none' }}</dd></div>
                                <div class="flex gap-1"><dt class="text-zinc-500">type:</dt><dd>{{ task.task_type_name }}</dd></div>
                                <div class="flex gap-1"><dt class="text-zinc-500">difficulty:</dt><dd>{{ task.difficulty }}</dd></div>
                            </dl>
                        </div>
                    </div>
                    <span class="shrink-0 text-xs text-zinc-400">{{ task.estimated_time_minutes }} min</span>
                </div>
            </template>
        </Panel>
        <RouterLink to="/tasks/create" class="mt-2 block px-3 py-3 text-xs text-lime-200 hover:bg-zinc-900">+ new task</RouterLink>
    </section>
</template>
