<script setup lang="ts">
defineProps<{ message: string }>()
</script>

<template>
    <p class="flex items-baseline gap-2 px-3 py-3 text-xs leading-5 text-zinc-500">
        <span class="text-zinc-600">—</span>
        <span>{{ message }}</span>
    </p>
</template>
