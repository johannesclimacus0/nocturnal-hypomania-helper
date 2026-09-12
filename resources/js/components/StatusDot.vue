<script setup lang="ts">
withDefaults(
    defineProps<
        { tone?: 'accent' | 'warning' | 'danger' | 'muted' | 'info' }>(),
    { tone: 'muted' }
)
</script>
<template>
    <span
        class="inline-flex items-center gap-2 whitespace-nowrap text-[0.6875rem]"
        :class="{ 'text-lime-200'
            : tone === 'accent', 'text-amber-300'
            : tone === 'warning', 'text-red-400'
            : tone === 'danger', 'text-zinc-400'
            : tone === 'muted', 'text-sky-300'
            : tone === 'info'
    }">
        <span class="size-1.5 shrink-0 rounded-full bg-current"></span>
        <slot />
    </span>
</template>
