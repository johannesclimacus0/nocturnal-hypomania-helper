import http from './http'
import { getAllPages } from './pagination'

export type TaskDifficulty = 'easy' | 'normal' | 'hard'

export type TaskStatus = 'active' | 'completed' | 'archived'

export interface Task {
    uuid: string
    title: string
    description: string|null
    estimated_time_minutes: number
    difficulty: TaskDifficulty
    status: TaskStatus
    task_type_uuid: string
    task_type_name: string
    area_uuid: string|null
    area_name: string|null
    category_uuid: string|null
    category_name: string|null
    last_selected_at: string|null
    last_skipped_at: string|null
    last_completed_at: string|null
    created_at: string|null
    updated_at: string|null
}

export interface AreaOption {
    uuid: string
    name: string
}

export interface CategoryOption {
    uuid: string
    name: string
}

export interface TaskTypeOption {
    uuid: string
    name: string
    slug: string
    is_system: boolean
}

export interface TaskFormOptions {
    areas: AreaOption[]
    categories: CategoryOption[]
    task_types: TaskTypeOption[]
}

export interface TaskPayload {
    title: string
    description: string|null
    estimated_time_minutes: number
    difficulty: TaskDifficulty
    task_type_uuid: string
    area_uuid: string|null
    category_uuid: string|null
}

export async function getTaskFormOptions(): Promise<TaskFormOptions> {
    const [areas, categories, taskTypes] = await Promise.all([
        http.get<{ data: AreaOption[] }>('/api/areas'),
        http.get<{ data: CategoryOption[] }>('/api/categories'),
        http.get<{ data: TaskTypeOption[] }>('/api/task-types'),
    ])

    return {
        areas: areas.data.data,
        categories: categories.data.data,
        task_types: taskTypes.data.data
    }
}

export async function getTask(uuid: string): Promise<Task> {
    return (await http.get<{data: Task}>(`/api/tasks/${uuid}`)).data.data
}
export async function createTask(data: TaskPayload): Promise<Task> {
    return (await http.post<{data: Task}>('/api/tasks', data)).data.data
}

export async function updateTask(uuid: string, data: TaskPayload): Promise<Task> {
    return (await http.patch<{data: Task}>(`/api/tasks/${uuid}`, data)).data.data
}

export async function deleteTask(uuid: string): Promise<void> {
    await http.delete(`/api/tasks/${uuid}`)
}

export async function getTasks(): Promise<Task[]> {
    return getAllPages<Task>('/api/tasks')
}
