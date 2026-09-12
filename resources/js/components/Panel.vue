<script setup lang="ts">
defineProps<{ title?: string }>()
</script>

<template>
    <section class="min-w-0">
        <header v-if="title || $slots.actions" class="flex flex-wrap items-center justify-between gap-2 border-y border-zinc-800 bg-zinc-900/60 px-3 py-2">
            <h2 class="text-xs text-zinc-400">{{ title }}</h2>
            <slot name="actions" />
        </header>
        <slot />
    </section>
</template>
