import http from './http'
import { getAllPages } from './pagination'
import type { Task, TaskDifficulty } from './tasks'

export interface SessionTask {
    uuid: string
    task_uuid: string
    status: 'selected' | 'completed' | 'skipped'
    position: number | null
    selected_at: string | null
    completed_at: string | null
    skipped_at: string | null
    task: Task | null
}
export interface Session {
    uuid: string
    started_at: string | null
    ended_at: string | null
    created_at: string | null
    tasks_count: number
    available_time_minutes: number
    difficulty: TaskDifficulty | null
    area_uuid: string | null
    area_name: string | null
    category_uuid: string | null
    category_name: string | null
    task_type_uuid: string | null
    task_type_name: string | null
    tasks?: SessionTask[]
}
export function getSessions(): Promise<Session[]> {
    return getAllPages<Session>('/api/sessions')
}

export async function getSession(uuid: string): Promise<Session> {
    return (await http.get<{ data: Session }>(`/api/sessions/${uuid}`)).data.data
}

export interface CreateSessionPayload {
    available_time_minutes: number
    difficulty: 'easy' | 'normal' | 'hard' | null
    area_uuid: string | null
    category_uuid: string | null
    task_type_uuid: string | null
}

export async function createSession(data: CreateSessionPayload): Promise<Session> {
    return (await http.post<{ data: Session }>('/api/sessions', data)).data.data
}

export async function finishSession(uuid: string): Promise<Session> {
    return (await http.patch<{ data: Session }>(`/api/sessions/${uuid}/finish`)).data.data
}

export async function addSessionTask(uuid: string, taskUuid: string, position: number): Promise<SessionTask> {
    return (await http.post<{ data: SessionTask }>(`/api/sessions/${uuid}/tasks`, { task_uuid: taskUuid, position })).data.data
}

export async function updateSessionTask(uuid: string, taskUuid: string, action: 'complete' | 'skip'): Promise<SessionTask> {
    return (await http.patch<{ data: SessionTask }>(`/api/sessions/${uuid}/tasks/${taskUuid}/${action}`)).data.data
}

export async function skipSessionTask(uuid: string, taskUuid: string): Promise<{ data: SessionTask; replacement: SessionTask | null }> {
    return (await http.patch<{ data: SessionTask; replacement: SessionTask | null }>(`/api/sessions/${uuid}/tasks/${taskUuid}/skip`)).data
}
