<script setup lang="ts">
import { useFontScale } from '../composables/useFontScale'

const {
    scale,
    minimum,
    maximum,
    decrease,
    increase,
    reset
} = useFontScale()
</script>

<template>
    <div class="flex items-center gap-1 text-xs">
        <span class="mr-2 text-zinc-500">font</span>
        <button type="button" :disabled="scale <= minimum" class="px-2 py-1 hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-30" @click="decrease">−</button>
        <button type="button" class="min-w-12 px-1 py-1 text-center tabular-nums text-zinc-400 hover:text-lime-200" @click="reset">{{ scale }}%</button>
        <button type="button" :disabled="scale >= maximum" class="px-2 py-1 hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-30" @click="increase">+</button>
    </div>
</template>
