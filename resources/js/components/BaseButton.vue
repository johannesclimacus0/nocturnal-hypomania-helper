<script setup lang="ts">
withDefaults(defineProps<{
    type?: 'button' | 'submit' | 'reset'
    loading?: boolean
    loadingText?: string
}>(), {
    type: 'button',
    loading: false,
    loadingText: 'Подождите...',
})
</script>

<template>
    <button :type="type" :disabled="loading" :aria-busy="loading">
        <slot v-if="!loading" />
        <template v-else>
            {{ loadingText }}
        </template>
    </button>
</template>
