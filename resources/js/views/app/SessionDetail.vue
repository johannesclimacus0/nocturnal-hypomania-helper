<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import EmptyState from '../../components/EmptyState.vue'
import StatusDot from '../../components/StatusDot.vue'
import { getTasks, type Task } from '../../api/tasks'
import { addSessionTask, finishSession, getSession, skipSessionTask, updateSessionTask, type Session } from '../../api/sessions'
import { duration, formatDate, requestMessage, shortUuid } from '../../utils/display'

const route = useRoute()
const uuid = computed(() => String(route.params.uuid ?? ''))
const session = ref<Session | null>(null)
const tasks = ref<Task[]>([])
const loading = ref(true)
const error = ref('')
const actionError = ref('')
const skipNotice = ref('')
const tasksError = ref('')
const busy = ref(false)
const selected = ref('')
const taskToAdd = ref('')
const search = ref('')
const now = ref(Date.now())

let timer: ReturnType<typeof setInterval> | undefined
let loadVersion = 0

const sequence = computed(() => session.value?.tasks ?? [])
const visibleSequence = computed(() => sequence.value.filter(task => task.status !== 'skipped'))
const current = computed(() => sequence.value.find(task => task.uuid === selected.value))
const completed = computed(() => sequence.value.filter(task => task.status === 'completed').length)

const available = computed(() => {
    const added = new Set(sequence.value.map(entry => entry.task_uuid))
    const query = search.value.trim().toLowerCase()
    return tasks.value.filter(task => task.status === 'active'
        && !added.has(task.uuid)
        && task.title.toLowerCase().includes(query))
})

const canAdd = computed(() => available.value.some(task => task.uuid === taskToAdd.value))
const activity = computed(() => {
    if (!session.value) return []

    const events: {
        date: string;
        label: string;
        key: string
    }[] = []

    if (session.value.started_at) {
        events.push({ date: session.value.started_at, label: 'Session started', key: 'started' })
    }
    for (const task of sequence.value) {
        for (const [status, date] of [['selected', task.selected_at], ['completed', task.completed_at], ['skipped', task.skipped_at]]) {
            if (date) events.push({ date, label: `${status} - ${task.task?.title ?? 'Deleted task'}`, key: `${task.uuid}-${status}` })
        }
    }
    if (session.value.ended_at) {
        events.push({ date: session.value.ended_at, label: 'Session finished', key: 'finished' })
    }
    return events.sort((a, b) => Date.parse(a.date) - Date.parse(b.date))
})

const loadAvailable = async function (): Promise<void> {
    const version = loadVersion
    tasksError.value = ''
    try {
        const result = await getTasks()
        if (version === loadVersion) tasks.value = result
    } catch {
        if (version === loadVersion) tasksError.value = 'Could not load available tasks.'
    }
}

const load = async function (): Promise<void> {
    const version = ++loadVersion
    loading.value = true
    error.value = ''
    actionError.value = ''
    skipNotice.value = ''
    tasksError.value = ''
    session.value = null
    tasks.value = []
    taskToAdd.value = ''
    try {
        const result = await getSession(uuid.value)
        if (version !== loadVersion) return
        session.value = result
        selected.value = result.tasks?.find(task => task.status === 'selected')?.uuid ?? result.tasks?.find(task => task.status !== 'skipped')?.uuid ?? ''
        if (!result.ended_at) await loadAvailable()
    } catch (cause) {
        if (version === loadVersion) error.value = requestMessage(cause, 'Could not load session.')
    } finally {
        if (version === loadVersion) loading.value = false
    }
}

const mutate = async function (action: () => Promise<void>): Promise<void> {
    if (busy.value || !session.value || session.value.ended_at) return
    busy.value = true
    actionError.value = ''
    try {
        await action()
    } catch (cause) {
        actionError.value = requestMessage(cause, 'Changes were not saved.')
    } finally {
        busy.value = false
    }
}

const add = async function (): Promise<void> {
    if (!canAdd.value) return
    await mutate(async () => {
        const target = session.value!
        const position = Math.max(0, ...sequence.value.map(task => task.position ?? 0)) + 1
        const entry = await addSessionTask(target.uuid, taskToAdd.value, position)
        if (session.value !== target) return
        target.tasks = [...(target.tasks ?? []), entry]
        target.tasks_count = target.tasks.length
        selected.value = entry.uuid
        taskToAdd.value = ''
    })
}

const change = async function (action: 'complete' | 'skip'): Promise<void> {
    const entry = current.value
    if (!entry || entry.status !== 'selected') return
    await mutate(async () => {
        const target = session.value!
        skipNotice.value = ''
        if (action === 'skip') {
            const { data: skipped, replacement } = await skipSessionTask(target.uuid, entry.uuid)
            if (session.value !== target) return
            target.tasks = (target.tasks ?? []).flatMap(task => task.uuid === skipped.uuid
                ? replacement ? [skipped, replacement] : [skipped]
                : [task])
            target.tasks_count = target.tasks.length
            selected.value = replacement?.uuid ?? target.tasks.find(task => task.status === 'selected')?.uuid
                ?? target.tasks.find(task => task.status === 'completed')?.uuid ?? ''
            if (!replacement) skipNotice.value = 'Task skipped. No matching replacement within the remaining time.'
            return
        }
        const result = await updateSessionTask(target.uuid, entry.uuid, action)
        if (session.value !== target) return

        target.tasks = target.tasks?.map(task => task.uuid === result.uuid ? result : task)
        selected.value = target.tasks?.find(task => task.status === 'selected')?.uuid ?? result.uuid
    })
}

const finish = async function (): Promise<void> {
    await mutate(async () => {
        const target = session.value!
        const result = await finishSession(target.uuid)
        if (session.value === target) {
            session.value = { ...result, tasks: target.tasks }
        }
    })
}
watch(uuid, load, { immediate: true })

onMounted(() => { timer = setInterval(() => { now.value = Date.now() }, 1000) })
onUnmounted(() => { clearInterval(timer); loadVersion++ })
</script>
<template>
    <PageHeader :title="session ? `RUN #${shortUuid(session.uuid)}` : 'session'" />
    <p v-if="loading" class="text-xs text-zinc-400">loading session...</p>
    <AlertMessage v-else-if="error" :message="error">
        <button type="button"  @click="load" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200">
            retry
        </button>
    </AlertMessage>
    <template v-else-if="session">
        <div class="mb-6 flex flex-wrap gap-7 border-y border-zinc-800 py-4 text-xs">
            <StatusDot :tone="session.ended_at ? 'muted' : 'accent'">{{ session.ended_at ? 'completed' : 'active' }}</StatusDot>
            <span>{{ session.ended_at ? 'duration' : 'elapsed' }} {{ duration(session.started_at, session.ended_at, now) }}</span>
            <span class="text-zinc-400">started {{ formatDate(session.started_at) }}</span>
        </div>
        <AlertMessage :message="actionError" class="mb-4" />
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="min-w-0 lg:border-r lg:border-zinc-700 lg:pr-5">
                <Panel title="task sequence">
                    <template #actions>
                        <span class="text-xs text-zinc-400">
                            {{ completed }} / {{ visibleSequence.length }} completed
                        </span>
                    </template>
                    <EmptyState v-if="!visibleSequence.length" message="no tasks in this session" />
                    <button v-for="(entry, index) in visibleSequence" :key="entry.uuid" @click="selected = entry.uuid" type="button" class="flex w-full items-center gap-3 border-b border-zinc-800 px-3 py-3 text-left text-xs last:border-b-0 hover:bg-zinc-900 focus-visible:outline-1 focus-visible:-outline-offset-1 focus-visible:outline-zinc-400" :class="{ 'bg-zinc-900': selected === entry.uuid }">
                        <span class="shrink-0 tabular-nums text-zinc-500">
                            {{ index + 1 }}
                        </span>
                        <span class="min-w-0 flex-1 break-words">
                            <span class="box-decoration-clone px-1 py-0.5"
                                  :class="selected === entry.uuid ? 'bg-zinc-300 text-zinc-950' : 'text-zinc-300'">
                                {{ entry.task?.title ?? 'Deleted task'}}
                            </span>
                        </span>
                        <span v-if="entry.status !== 'selected'"
                              class="shrink-0"
                              :class="entry.status === 'completed' ? 'text-lime-200' : 'text-amber-300'"
                        >{{ entry.status }}
                        </span>
                    </button>
                </Panel>
                <p v-if="skipNotice" role="status" class="mt-3 text-xs text-zinc-400">{{ skipNotice }}</p>
                <form v-if="!session.ended_at" @submit.prevent="add" class="mt-5 space-y-3">
                    <AlertMessage v-if="tasksError" :message="tasksError">
                        <button type="button" @click="loadAvailable" class="underline decoration-zinc-600 underline-offset-4 hover:text-lime-200">
                            retry
                        </button>
                    </AlertMessage>
                    <template v-else>
                        <BaseInput id="session-task-search"
                                   v-model="search"
                                   label="find a task"
                                   type="search"
                                   placeholder="Search title..."
                        />
                        <BaseSelect id="session-task"
                                    v-model="taskToAdd"
                                    label="add task"
                                    :disabled="busy"
                        >
                            <option value="">choose a task</option>
                            <option v-for="task in available" :key="task.uuid" :value="task.uuid">
                                {{ task.title }} - {{ task.estimated_time_minutes }} min
                            </option>
                        </BaseSelect>
                        <p v-if="!available.length" class="text-xs text-zinc-400">No matching active tasks. <RouterLink to="/tasks/create" class="text-lime-200">create a task</RouterLink></p>
                        <BaseButton type="submit" :disabled="busy || !canAdd">+ add task</BaseButton>
                    </template>
                </form>
                <BaseButton v-if="!session.ended_at" class="mt-5" :disabled="busy" @click="finish">finish session</BaseButton>
            </section>
            <section v-if="current" class="min-w-0">
                <p v-if="current.status !== 'selected'" class="mb-3 text-xs" :class="current.status === 'completed' ? 'text-lime-200' : 'text-amber-300'">{{ current.status }}</p>
                <template v-if="current.task">
                    <RouterLink :to="`/tasks/${current.task.uuid}`" class="break-words text-sm text-lime-200">
                        {{ current.task.title }}
                    </RouterLink>
                    <p class="mt-4 whitespace-pre-wrap break-words text-xs leading-6 text-zinc-400">
                        {{ current.task.description || 'No description' }}
                    </p>
                    <dl class="mt-5 grid grid-cols-[6.25rem_1fr] gap-y-2 text-xs">
                        <dt class="text-zinc-500">type</dt>
                        <dd>{{ current.task.task_type_name }}</dd>
                        <dt class="text-zinc-500">difficulty</dt>
                        <dd>{{ current.task.difficulty }}</dd>
                        <dt class="text-zinc-500">estimate</dt>
                        <dd>{{ current.task.estimated_time_minutes }} min</dd>
                    </dl>
                </template>
                <p v-else class="text-xs text-zinc-400">This task is no longer available.</p>
                <div v-if="!session.ended_at && current.status === 'selected'" class="mt-6 flex gap-3 border-t border-zinc-800 pt-3">
                    <BaseButton :disabled="busy" @click="change('complete')" class="text-lime-200">
                        complete
                    </BaseButton>
                    <BaseButton :disabled="busy" @click="change('skip')">
                        skip
                    </BaseButton>
                </div>
            </section>
        </div>
        <section class="mt-7">
            <h2 class="mb-4 text-xs text-zinc-400">activity</h2>
            <div v-for="event in activity" :key="event.key" class="flex flex-wrap gap-3 py-1.5 text-xs">
                <time :datetime="event.date" class="text-zinc-400">
                    {{ formatDate(event.date) }}
                </time>
                <span>{{ event.label }}</span>
            </div>
        </section>
    </template>
    <RouterLink to="/sessions" class="mt-5 inline-block text-xs text-lime-200">all sessions</RouterLink>
</template>
