<script setup lang="ts">
const props = defineProps<{
    id: string
    label: string
    modelValue: string
    error?: string
    hint?: string
    modelModifiers?: Record<string, boolean>
}>()
defineOptions({
    inheritAttrs: false
})
const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

function updateValue(event: Event) {
    const input = event.target as HTMLInputElement
    const value = props.modelModifiers?.trim ? input.value.trim() : input.value

    emit('update:modelValue', value)
}
</script>

<template>
    <div class="form-field">
        <label :for="id">
            {{ label }}
        </label>
        <input
            :id="id"
            :name="id"
            :value="modelValue"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error ? `${id}-error` : hint ? `${id}-hint` : undefined"
            v-bind="$attrs"
            @input="updateValue"
        >
        <p v-if="hint" :id="`${id}-hint`" class="form-field__hint">
            {{ hint }}
        </p>
        <p v-if="error" :id="`${id}-error`" role="alert">
            {{ error }}
        </p>
    </div>
</template>
