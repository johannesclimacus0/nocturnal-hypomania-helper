import axios from 'axios'

export const shortUuid = (uuid: string, length : number = 8): string => `${uuid.slice(0, length)}...`

const dateFormat = new Intl.DateTimeFormat(
    'en-GB', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })

export const formatDate = (date: string | null): string => date
    ? dateFormat.format(new Date(date))
    : '—'

export function duration(start: string | null, end: string | null, now = Date.now()): string {
    return start ? `${Math.max(0, Math.floor(((end ? Date.parse(end) : now) - Date.parse(start)) / 60000))} min` : '—'
}

export function requestMessage(error: unknown, fallback: string): string {
    if (axios.isAxiosError(error)) {
        if (error.response?.status === 404) return 'Not found'
        if (error.response?.status === 422) {
            const errors = error.response.data?.errors as Record<string, string[]> | undefined
            return (errors && Object.values(errors).flat()[0]) || fallback
        }
    }
    return fallback
}
