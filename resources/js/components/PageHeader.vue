<script setup lang="ts">
defineProps<{ title: string }>()
</script>

<template>
    <header class="mb-4 border-b border-zinc-800 pb-3">
        <h1 class="break-words text-sm font-medium">{{ title }}</h1>
        <div v-if="$slots.default" class="mt-2 flex flex-wrap gap-3 text-xs"><slot /></div>
    </header>
</template>
