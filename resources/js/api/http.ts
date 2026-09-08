import axios from 'axios'

const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content

const http = axios.create({
    baseURL: '/',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
})

export default http
