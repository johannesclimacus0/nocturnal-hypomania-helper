<script setup lang="ts">
const props = defineProps<{
    id: string
    label: string
    modelValue: string
    error?: string
    modelModifiers?: Record<string, boolean>
}>()

defineOptions({
    inheritAttrs: false
})

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const updateValue = function (event: Event) {
    const input = event.target as HTMLInputElement
    const value = props.modelModifiers?.trim ? input.value.trim() : input.value

    emit('update:modelValue', value)
}
</script>

<template>
    <div class="space-y-2">
        <label class="block text-xs text-zinc-400" :for="id">
            {{ label }}
        </label>
        <input :class="{ 'border-red-300/60': error }" class="w-full min-w-0 border border-zinc-700 bg-zinc-950 px-2.5 py-2 text-xs text-zinc-200 placeholder:text-zinc-600 focus:border-lime-200 focus:outline-none focus:ring-1 focus:ring-lime-200/40"
            :id="id"
            :name="id"
            :value="modelValue"
            v-bind="$attrs"
            @input="updateValue"
        >
        <p v-if="error" class="flex items-start gap-2 text-[0.6875rem] leading-5 text-zinc-400">
            <span class="shrink-0 text-red-300">!</span>
            <span>{{ error }}</span>
        </p>
    </div>
</template>
