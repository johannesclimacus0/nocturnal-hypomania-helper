import http from './http'

interface Page<T> {
    data: T[]
    meta: { last_page: number }
}

export async function getAllPages<T>(url: string): Promise<T[]> {
    const items: T[] = []
    let page = 1
    let lastPage = 1

    do {
        const { data } = await http.get<Page<T>>(url, { params: { page } })
        items.push(...data.data)
        lastPage = data.meta.last_page
        page++
    } while (page <= lastPage)

    return items
}
