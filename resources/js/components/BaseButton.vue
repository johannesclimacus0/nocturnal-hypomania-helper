<script setup lang="ts">
defineOptions({ inheritAttrs: false })
withDefaults(defineProps<{
    type?: 'button' | 'submit' | 'reset'
    loading?: boolean
    loadingText?: string
}>(), {
    type: 'button',
    loading: false,
    loadingText: 'Please wait...',
})
</script>

<template>
    <button class="inline-flex min-h-8 items-center justify-center gap-2 border border-zinc-700 px-3 py-1 text-xs hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40" :type="type" :disabled="loading" v-bind="$attrs">
        <slot v-if="!loading" />
        <template v-else>
            {{ loadingText }}
        </template>
    </button>
</template>
