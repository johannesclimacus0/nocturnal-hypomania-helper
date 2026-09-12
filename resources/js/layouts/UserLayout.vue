<script setup lang="ts">
import FontScaleControl from '../components/FontScaleControl.vue'
</script>

<template>
    <div class="flex min-h-dvh flex-col">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-lime-200 focus:p-3 focus:text-zinc-950">
            К содержимому
        </a>
        <header class="flex flex-wrap items-center gap-x-6 gap-y-1 border-b border-zinc-700 px-4 sm:px-6">
            <nav class="flex flex-wrap items-center gap-x-4 text-xs">
                <RouterLink to="/" class="relative pl-3 before:absolute before:left-0 before:text-lime-200 before:opacity-0 before:content-['›'] py-3 text-zinc-400 hover:text-zinc-100" active-class="" exact-active-class="text-zinc-100! before:opacity-100!">
                    tonight
                </RouterLink>
                <RouterLink to="/tasks" class="relative pl-3 before:absolute before:left-0 before:text-lime-200 before:opacity-0 before:content-['›'] py-3 text-zinc-400 hover:text-zinc-100" active-class="text-zinc-100! before:opacity-100!">
                    tasks
                </RouterLink>
                <RouterLink to="/sessions" class="relative pl-3 before:absolute before:left-0 before:text-lime-200 before:opacity-0 before:content-['›'] py-3 text-zinc-400 hover:text-zinc-100" active-class="text-zinc-100! before:opacity-100!">
                    sessions
                </RouterLink>
                <span class="h-4 border-l border-zinc-700"></span>
                <RouterLink to="/areas" class="relative pl-3 before:absolute before:left-0 before:text-lime-200 before:opacity-0 before:content-['›'] py-3 text-zinc-400 hover:text-zinc-100" active-class="text-zinc-100! before:opacity-100!">
                    areas
                </RouterLink>
                <RouterLink to="/categories" class="relative pl-3 before:absolute before:left-0 before:text-lime-200 before:opacity-0 before:content-['›'] py-3 text-zinc-400 hover:text-zinc-100" active-class="text-zinc-100! before:opacity-100!">
                    categories
                </RouterLink>
                <RouterLink to="/task-types" class="relative pl-3 before:absolute before:left-0 before:text-lime-200 before:opacity-0 before:content-['›'] py-3 text-zinc-400 hover:text-zinc-100" active-class="text-zinc-100! before:opacity-100!">
                    types
                </RouterLink>
            </nav>
        </header>
        <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-[90rem] min-w-0 flex-1 px-4 py-5 sm:px-6">
            <slot />
        </main>
        <footer class="flex flex-wrap items-center justify-between gap-4 border-t border-zinc-700 px-4 py-2 text-[0.6875rem] sm:px-6">
            <div class="min-w-0 [&>div]:flex [&>div]:flex-wrap [&>div]:gap-x-4 [&>div]:space-y-0">
                <slot name="user" />
            </div>
            <div class="flex flex-wrap items-center gap-5">
                <FontScaleControl />
                <slot name="actions" />
            </div>
        </footer>
    </div>
</template>
