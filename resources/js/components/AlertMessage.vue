<script setup lang="ts">
withDefaults(defineProps<{
    message?: string
    type?: 'error' | 'success'
}>(), {
    type: 'error',
})
</script>

<template>
    <div v-if="message"
         class="flex flex-wrap items-start gap-x-3 gap-y-2 border border-zinc-800 bg-zinc-900/40 px-3 py-2.5 text-xs leading-5">
        <span class="shrink-0 font-mono" :class="type === 'error' ? 'text-red-300' : 'text-lime-200'">{{ type === 'error' ? '!' : '+' }}</span>
        <p class="min-w-0 flex-1 break-words text-zinc-300">{{ message }}</p>
        <div v-if="$slots.default" class="shrink-0 text-zinc-400"><slot /></div>
    </div>
</template>
