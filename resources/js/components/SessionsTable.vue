<script setup lang="ts">
import type { Session } from '../api/sessions'
import StatusDot from './StatusDot.vue'
import EmptyState from './EmptyState.vue'
import { formatDate, duration, shortUuid } from '../utils/display'
defineProps<{ sessions: Session[] }>()
</script>
<template>
    <EmptyState v-if="!sessions.length" message="no sessions yet" />
    <div v-else class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs [&_th]:border-b [&_th]:border-zinc-800 [&_th]:px-3 [&_th]:py-2 [&_th]:font-normal [&_th]:text-zinc-500 [&_td]:border-b [&_td]:border-zinc-800 [&_td]:px-3 [&_td]:py-3 [&_tbody_tr:hover]:bg-zinc-900">
            <thead>
                <tr>
                    <th>run</th>
                    <th>status</th>
                    <th>duration</th>
                    <th>tasks</th>
                    <th>started</th>
                    <th>finished</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="session in sessions" :key="session.uuid">
                    <td>
                        <RouterLink :to="`/sessions/${session.uuid}`"
                                    :title="session.uuid"
                                    class="whitespace-nowrap hover:text-lime-200"
                        >RUN #{{ shortUuid(session.uuid) }}
                        </RouterLink>
                    </td>
                    <td>
                        <StatusDot :tone="session.ended_at ? 'muted' : 'accent'">
                            {{ session.ended_at ? 'completed' : 'active' }}
                        </StatusDot>
                    </td>
                    <td class="whitespace-nowrap">
                        {{ duration(session.started_at, session.ended_at) }}
                    </td>
                    <td>{{ session.tasks_count }}</td>
                    <td class="whitespace-nowrap text-zinc-400">
                        {{ formatDate(session.started_at) }}
                    </td>
                    <td class="whitespace-nowrap text-zinc-400">
                        {{ formatDate(session.ended_at) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
