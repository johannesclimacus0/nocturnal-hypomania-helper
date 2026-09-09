<script setup lang="ts">
defineProps<{
    title: string
    description?: string
}>()
</script>

<template>
    <main class="auth-layout">
        <section class="auth-layout__card" aria-labelledby="auth-page-title">
            <header class="auth-layout__header">
                <h1 id="auth-page-title">
                    {{ title }}
                </h1>
                <p v-if="description">
                    {{ description }}
                </p>
            </header>

            <slot />

            <nav v-if="$slots.footer" aria-label="Навигация авторизации">
                <slot name="footer" />
            </nav>
        </section>
    </main>
</template>
