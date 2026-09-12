<script setup lang="ts">
import { ref } from 'vue'
import { useEchoNotification } from '@laravel/echo-vue'
import { shortUuid } from '../utils/display'

const props = defineProps<{ userUuid: string }>()

interface SessionResultNotification {
    session_uuid: string
    result: {
        total: number
        completed: number
        skipped: number
        unfinished: number
        duration_minutes: number
    }
}

const results = ref<SessionResultNotification[]>([])
const received = new Set<string>()

useEchoNotification<SessionResultNotification>(`users.${props.userUuid}`, notification => {
    if (received.has(notification.session_uuid)) return
    received.add(notification.session_uuid)
    results.value.push(notification)
}, 'night-session.result')
</script>

<template>
    <aside class="pointer-events-none fixed inset-x-4 bottom-4 z-50 sm:left-auto sm:right-6 sm:w-96"
           aria-live="polite" aria-atomic="true">
        <section v-if="results[0]" class="pointer-events-auto border border-zinc-700 bg-zinc-950 p-4 font-mono text-xs shadow-lg">
            <div class="flex items-start justify-between gap-4 border-b border-zinc-800 pb-3">
                <div>
                    <p class="text-lime-200">session finished</p>
                    <RouterLink :to="`/sessions/${results[0].session_uuid}`"
                                class="mt-1 inline-block text-zinc-400 hover:text-lime-200">
                        RUN #{{ shortUuid(results[0].session_uuid) }}
                    </RouterLink>
                </div>
                <button type="button" aria-label="Dismiss session results"
                        class="px-1 text-zinc-500 hover:text-zinc-200"
                        @click="results.shift()">[x]</button>
            </div>
            <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 tabular-nums">
                <dt class="text-zinc-400">completed</dt><dd class="text-right text-lime-200">{{ results[0].result.completed }}</dd>
                <dt class="text-zinc-400">skipped</dt><dd class="text-right text-amber-300">{{ results[0].result.skipped }}</dd>
                <dt class="text-zinc-400">unfinished</dt><dd class="text-right text-zinc-300">{{ results[0].result.unfinished }}</dd>
                <dt class="text-zinc-400">total</dt><dd class="text-right text-zinc-300">{{ results[0].result.total }}</dd>
                <dt class="text-zinc-400">duration</dt><dd class="text-right text-zinc-300">{{ results[0].result.duration_minutes }} min</dd>
            </dl>
            <p v-if="results.length > 1" class="mt-3 text-zinc-500">{{ results.length - 1 }} more results</p>
        </section>
    </aside>
</template>
