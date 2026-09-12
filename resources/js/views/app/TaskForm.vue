<script setup lang="ts">
import AlertMessage from '../../components/AlertMessage.vue'
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import PageHeader from '../../components/PageHeader.vue'
import Panel from '../../components/Panel.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import { createTask, getTask, getTaskFormOptions, updateTask, type AreaOption, type CategoryOption, type TaskDifficulty, type TaskPayload, type TaskTypeOption } from '../../api/tasks'

const props = withDefaults(
    defineProps<{ editing?: boolean }>(),
    { editing: false }
)

const route = useRoute()
const router = useRouter()

const form = reactive<{
    title: string
    description: string
    estimateTime: string
    difficulty: TaskDifficulty
    areaUuid: string
    categoryUuid: string
    taskTypeUuid: string
}>({
    title: '',
    description: '',
    estimateTime: '30',
    difficulty: 'normal',
    areaUuid: '',
    categoryUuid: '',
    taskTypeUuid: '',
})

const areas = ref<AreaOption[]>([])
const categories = ref<CategoryOption[]>([])
const taskTypes = ref<TaskTypeOption[]>([])
const errors = ref<Record<string, string[]>>({})
const loading = ref(true)
const submitting = ref(false)
const pageError = ref('')

const taskUuid = computed(() => String(route.params.uuid ?? ''))

const loadForm = async function (): Promise<void> {
    loading.value = true
    try {
        const options = await getTaskFormOptions()
        areas.value = options.areas
        categories.value = options.categories
        taskTypes.value = options.task_types
        if (props.editing) {
            const task = await getTask(taskUuid.value)
            form.title = task.title
            form.description = task.description ?? ''
            form.estimateTime = String(task.estimated_time_minutes)
            form.difficulty = task.difficulty
            form.taskTypeUuid = task.task_type_uuid
            form.areaUuid = task.area_uuid ?? ''
            form.categoryUuid = task.category_uuid ?? ''
        }
    } catch (error) {
        pageError.value = axios.isAxiosError(error) && error.response?.status === 404 ? 'Task not found.' : 'Could not load the form. Reload the page to try again.'
    } finally {
        loading.value = false
    }
}

const submit = async function (): Promise<void> {
    if (loading.value || submitting.value) return
    errors.value = {}
    pageError.value = ''
    submitting.value = true
    const payload: TaskPayload = {
        title: form.title.trim(),
        description: form.description.trim() || null,
        estimated_time_minutes: Number(form.estimateTime),
        difficulty: form.difficulty,
        task_type_uuid: form.taskTypeUuid,
        area_uuid: form.areaUuid || null,
        category_uuid: form.categoryUuid || null,
    }
    try {
        const task = props.editing ? await updateTask(taskUuid.value, payload) : await createTask(payload)
        await router.push(`/tasks/${task.uuid}`)
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) errors.value = error.response.data.errors ?? {}
        else pageError.value = props.editing ? 'Changes were not saved. Try again.' : 'Task was not created. Try again.'
    } finally {
        submitting.value = false
    }
}

onMounted(loadForm)
</script>

<template>
    <PageHeader :title="editing ? 'edit task' : 'new task'" />
    <AlertMessage :message="pageError" class="mb-4" />
    <p v-if="loading" class="py-3 text-xs text-zinc-500">loading form...</p>
    <form v-else class="max-w-2xl" @submit.prevent="submit">
        <Panel title="task">
            <div class="grid gap-y-4 py-4 sm:[&>div]:grid sm:[&>div]:grid-cols-[8.75rem_minmax(0,1fr)] sm:[&>div]:gap-x-4 sm:[&>div]:space-y-0 sm:[&>div>p]:col-start-2 sm:[&>div>p]:mt-1">
                <BaseInput v-model="form.title"
                           id="title"
                           label="title *"
                           placeholder="Task title"
                           maxlength="255"
                           :error="errors.title?.[0]"
                           required
                />
                <BaseTextarea v-model="form.description"
                              id="description"
                              label="description"
                              rows="6"
                              placeholder="Description"
                              :error="errors.description?.[0]"
                />
                <BaseInput v-model="form.estimateTime"
                           id="estimate"
                           label="estimate / minutes *"
                           type="number" min="1"
                           :error="errors.estimated_time_minutes?.[0]"
                           required />
                <BaseSelect v-model="form.difficulty"
                            id="difficulty"
                            label="difficulty *"
                            :error="errors.difficulty?.[0]"
                            required
                >
                    <option value="easy">easy</option>
                    <option value="normal">normal</option>
                    <option value="hard">hard</option>
                </BaseSelect>
            </div>
        </Panel>
        <Panel title="taxonomy">
            <div class="grid gap-y-4 py-4 sm:[&>div]:grid sm:[&>div]:grid-cols-[8.75rem_minmax(0,1fr)] sm:[&>div]:items-center sm:[&>div]:gap-x-4 sm:[&>div]:space-y-0 sm:[&>div>p]:col-start-2 sm:[&>div>p]:mt-1">
                <BaseSelect v-model="form.areaUuid"
                            id="area"
                            label="area"
                            :error="errors.area_uuid?.[0]"
                >
                    <option value="">none</option>
                    <option v-for="area in areas" :key="area.uuid" :value="area.uuid">
                        {{ area.name }}
                    </option>
                </BaseSelect>
                <BaseSelect v-model="form.categoryUuid"
                            id="category"
                            label="category"
                            :error="errors.category_uuid?.[0]"
                >
                    <option value="">none</option>
                    <option v-for="category in categories" :key="category.uuid" :value="category.uuid">
                        {{ category.name }}
                    </option>
                </BaseSelect>
                <BaseSelect v-model="form.taskTypeUuid"
                            id="type"
                            label="type *"
                            :error="errors.task_type_uuid?.[0]"
                            required
                >
                    <option value="">select type</option>
                    <option v-for="type in taskTypes" :key="type.uuid" :value="type.uuid">
                        {{ type.name }}
                    </option>
                </BaseSelect>
            </div>
        </Panel>
        <div class="flex flex-wrap items-center gap-4 border-t border-zinc-800 pt-4 sm:pl-[9.75rem]">
            <BaseButton type="submit" :loading="submitting" loading-text="saving..." class="text-lime-200">
                {{ editing ? 'save changes' : 'create task' }}
            </BaseButton>
            <RouterLink to="/tasks" class="text-xs text-zinc-400 hover:text-zinc-200">
                cancel
            </RouterLink>
        </div>
    </form>
</template>
