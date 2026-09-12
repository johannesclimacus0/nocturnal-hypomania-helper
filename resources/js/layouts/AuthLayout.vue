<script setup lang="ts">
defineProps<{ title: string; description?: string }>()
</script>

<template>
    <main class="flex min-h-dvh flex-col items-center justify-center px-5 py-12">
        <section class="w-full max-w-sm">
            <header class="mb-6 border-b border-zinc-800 pb-5">
                <h1 id="auth-page-title" class="text-lg">{{ title }}</h1>
                <p v-if="description" class="mt-2 text-xs text-zinc-400">{{ description }}</p>
            </header>
            <div class="space-y-4">
                <slot />
            </div>
            <nav v-if="$slots.footer" class="mt-6 flex flex-wrap justify-between gap-3 border-t border-zinc-800 pt-4 text-xs text-lime-200">
                <slot name="footer" />
            </nav>
        </section>
    </main>
</template>
