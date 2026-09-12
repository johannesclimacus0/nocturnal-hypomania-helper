import { ref } from 'vue'

const storageKey = 'rpn.font-scale'
const minimum = 80
const maximum = 200
const step = 10
const scale = ref(100)

function applyScale(): void {
    if (scale.value === 100) {
        document.documentElement.style.removeProperty('font-size')
    } else {
        document.documentElement.style.fontSize = `${scale.value}%`
    }
}

export function initializeFontScale(): void {
    try {
        const saved = Number(localStorage.getItem(storageKey))
        if (Number.isInteger(saved) && saved >= minimum && saved <= maximum && saved % step === 0) {
            scale.value = saved
        }
    } catch {}
    applyScale()
}

function setScale(value: number): void {
    scale.value = Math.min(maximum, Math.max(minimum, value))
    applyScale()
    try {
        localStorage.setItem(storageKey, String(scale.value))
    } catch {}
}

export function useFontScale() {
    return {
        scale,
        minimum,
        maximum,
        decrease: () => setScale(scale.value - step),
        increase: () => setScale(scale.value + step),
        reset: () => setScale(100),
    }
}
