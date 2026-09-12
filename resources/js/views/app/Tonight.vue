<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, ref, reactive } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import SessionsTable from '../../components/SessionsTable.vue'
import { shortUuid } from '../../utils/display'
import { createSession, getSessions, type Session } from '../../api/sessions'
import {
    getTaskFormOptions,
    type AreaOption,
    type CategoryOption,
    type TaskDifficulty,
    type TaskTypeOption
} from '../../api/tasks'

const router = useRouter()
const sessions = ref<Session[]>([])
const loading = ref(true)
const starting = ref(false)
const error = ref('')
const startError = ref('')
const errors = ref<Record<string, string[]>>({})

const areas = ref<AreaOption[]>([])
const categories = ref<CategoryOption[]>([])
const taskTypes = ref<TaskTypeOption[]>([])

const form = reactive<{
    availableTimeMinutes: string
    difficulty: '' | TaskDifficulty
    areaUuid: string
    categoryUuid: string
    taskTypeUuid: string
}>({
    availableTimeMinutes: '60',
    difficulty: 'normal',
    areaUuid: '',
    categoryUuid: '',
    taskTypeUuid: '',
})

const active = computed(() => sessions.value.filter(session => !session.ended_at))

const load = async function (): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        sessions.value = await getSessions()
    } catch { error.value = 'Could not load sessions.' }
    finally { loading.value = false }
}

const loadOptions = async function (): Promise<void> {
    const options = await getTaskFormOptions()
    areas.value = options.areas
    categories.value = options.categories
    taskTypes.value = options.task_types
}

const start = async function (): Promise<void> {
    if (starting.value) return

    starting.value = true
    startError.value = ''
    errors.value = {}
    try {
        const session = await createSession({
            available_time_minutes: Number(form.availableTimeMinutes),
            difficulty: form.difficulty || null,
            area_uuid: form.areaUuid || null,
            category_uuid: form.categoryUuid || null,
            task_type_uuid: form.taskTypeUuid || null,
        })
        sessions.value.unshift(session)
        await router.push(`/sessions/${session.uuid}`)
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {}
            return
        }
        startError.value = 'Session could not be started.'
    }
    finally {
        starting.value = false
    }
}

onMounted(async () => {
    await Promise.all([load(), loadOptions()])
})
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
            </template>
            <form class="space-y-3" @submit.prevent="start">
                <BaseInput id="available-time"
                           v-model="form.availableTimeMinutes"
                           label="available time (minutes)"
                           type="number"
                           min="1"
                           required
                           :error="errors.available_time_minutes?.[0]"
                />
                <BaseSelect id="session-difficulty"
                            v-model="form.difficulty"
                            label="difficulty"
                            :error="errors.difficulty?.[0]"
                >
                    <option value="">any difficulty</option>
                    <option value="easy">easy</option>
                    <option value="normal">normal</option>
                    <option value="hard">hard</option>
                </BaseSelect>
                <BaseSelect id="session-area"
                            v-model="form.areaUuid"
                            label="area" :error="errors.area_uuid?.[0]"
                >
                    <option value="">any area</option>
                    <option v-for="area in areas" :key="area.uuid" :value="area.uuid">
                        {{ area.name }}
                    </option>
                </BaseSelect>
                <BaseSelect id="session-category"
                            v-model="form.categoryUuid"
                            label="category"
                            :error="errors.category_uuid?.[0]"
                >
                    <option value="">any category</option>
                    <option v-for="category in categories"
                            :key="category.uuid"
                            :value="category.uuid"
                    >{{ category.name }}
                    </option>
                </BaseSelect>
                <BaseSelect id="session-task-type" v-model="form.taskTypeUuid" label="task type" :error="errors.task_type_uuid?.[0]">
                    <option value="">any task type</option>
                    <option v-for="type in taskTypes" :key="type.uuid" :value="type.uuid">
                        {{ type.name }}
                    </option>
                </BaseSelect>
                <BaseButton type="submit"
                            :loading="starting"
                            loading-text="starting..."
                            class="text-lime-200"
                >start session
                </BaseButton>
            </form>
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
