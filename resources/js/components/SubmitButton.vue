<script setup lang="ts">
import BaseButton from './BaseButton.vue'

withDefaults(
    defineProps<{
        loading?: boolean
        loadingText?: string
    }>(),
    {
        loading: false,
        loadingText: 'Please wait...',
    },
)
</script>

<template>
    <BaseButton type="submit" :loading="loading" :loading-text="loadingText">
        <slot />
    </BaseButton>
</template>
