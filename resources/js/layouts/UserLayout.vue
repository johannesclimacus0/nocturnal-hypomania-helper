<script setup lang="ts">
defineProps<{
    title: string
}>()
</script>

<template>
    <main class="user-layout">
        <header class="user-layout__header">
            <h1>{{ title }}</h1>
            <slot name="actions" />
        </header>

        <section class="user-layout__content">
            <slot />
        </section>
    </main>
</template>
