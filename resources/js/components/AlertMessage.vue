<script setup lang="ts">
withDefaults(defineProps<{
    message?: string
    type?: 'error' | 'success'
}>(), {
    type: 'error',
})
</script>

<template>
    <p
        v-if="message"
        :role="type === 'error' ? 'alert' : 'status'"
        :class="['alert', `alert--${type}`]"
    >
        {{ message }}
    </p>
</template>
