import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import { nextTick, ref } from 'vue'
import axios from 'axios'
import { useSprunjer } from '../../composables/useSprunjer'

const flushPromises = async () => {
    await Promise.resolve()
    await Promise.resolve()
}

describe('useSprunjer', () => {
    beforeEach(() => {
        vi.restoreAllMocks()
    })

    afterEach(() => {
        vi.clearAllMocks()
    })

    test('initializes defaults and maps successful fetch response', async () => {
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: {
                count: 25,
                count_filtered: 12,
                rows: [{ id: 1 }],
                listable: { status: [{ value: 'active', text: 'Active' }] },
                sortable: ['id'],
                filterable: ['status']
            }
        } as any)

        const sprunjer = useSprunjer('/api/users', { name: 'asc' }, { status: 'active' }, 10, 0)

        // Initial values before fetch
        expect(sprunjer.size.value).toBe(10)
        expect(sprunjer.page.value).toBe(0)
        expect(sprunjer.sorts.value).toEqual({ name: 'asc' })
        expect(sprunjer.filters.value).toEqual({ status: 'active' })
        expect(sprunjer.count.value).toBe(0)
        expect(sprunjer.countFiltered.value).toBe(0)
        expect(sprunjer.first.value).toBe(0)
        expect(sprunjer.last.value).toBe(0)
        expect(sprunjer.totalPages.value).toBe(0)

        await flushPromises()

        //
        expect(sprunjer.count.value).toBe(25)
        expect(sprunjer.countFiltered.value).toBe(12)
        expect(sprunjer.rows.value).toEqual([{ id: 1 }])
        expect(sprunjer.data.value.listable).toEqual({
            status: [{ value: 'active', text: 'Active' }]
        })
        expect(sprunjer.data.value.sortable).toEqual(['id'])
        expect(sprunjer.data.value.filterable).toEqual(['status'])
        expect(sprunjer.totalPages.value).toBe(1)
        expect(sprunjer.first.value).toBe(1)
        expect(sprunjer.last.value).toBe(10)
    })

    test('applies fallback values for optional API keys', async () => {
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: {
                count: 2,
                count_filtered: 2,
                rows: [{ id: 1 }, { id: 2 }]
            }
        } as any)

        const sprunjer = useSprunjer('/api/users')
        await flushPromises()

        expect(sprunjer.data.value.listable).toEqual({})
        expect(sprunjer.data.value.sortable).toEqual([])
        expect(sprunjer.data.value.filterable).toEqual([])
    })

    test('captures fetch error and resets loading state', async () => {
        vi.spyOn(axios, 'get').mockRejectedValue({
            response: {
                data: {
                    title: 'Fetch failed',
                    description: 'Could not fetch users',
                    status: 500
                }
            }
        })

        const sprunjer = useSprunjer('/api/users')
        await flushPromises()

        expect(sprunjer.error.value).toEqual({
            title: 'Fetch failed',
            description: 'Could not fetch users',
            status: 500
        })
        expect(sprunjer.loading.value).toBe(false)
    })

    test('toggles sort order across asc, desc and null', () => {
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: { count: 0, count_filtered: 0, rows: [] }
        } as any)

        const sprunjer = useSprunjer('/api/users', { name: 'asc' })

        sprunjer.toggleSort('name')
        expect(sprunjer.sorts.value.name).toBe('desc')

        sprunjer.toggleSort('name')
        expect(sprunjer.sorts.value.name).toBeNull()

        sprunjer.toggleSort('name')
        expect(sprunjer.sorts.value.name).toBe('asc')
    })

    test('handles computed pagination when size is all', async () => {
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: {
                count: 50,
                count_filtered: 42,
                rows: []
            }
        } as any)

        const sprunjer = useSprunjer('/api/users', {}, {}, 'all')
        await flushPromises()

        expect(sprunjer.totalPages.value).toBe(0)
        expect(sprunjer.first.value).toBe(1)
        expect(sprunjer.last.value).toBe(42)
    })

    test('falls back to default computed values when response data fields are missing', async () => {
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: {
                count: 1,
                count_filtered: 1,
                rows: [{ id: 1 }]
            }
        } as any)

        const sprunjer = useSprunjer('/api/users')
        await flushPromises()

        sprunjer.data.value = {
            count: undefined as any,
            count_filtered: undefined as any,
            rows: undefined as any,
            listable: {},
            sortable: [],
            filterable: []
        }

        expect(sprunjer.count.value).toBe(0)
        expect(sprunjer.countFiltered.value).toBe(0)
        expect(sprunjer.rows.value).toEqual([])
        expect(sprunjer.totalPages.value).toBe(0)
        expect(sprunjer.first.value).toBe(0)
        expect(sprunjer.last.value).toBe(0)

        sprunjer.size.value = 'all'
        expect(sprunjer.first.value).toBe(1)
        expect(sprunjer.last.value).toBe(0)
    })

    test('downloads CSV and cleans temporary blob URL', async () => {
        const getSpy = vi.spyOn(axios, 'get')
        getSpy
            .mockResolvedValueOnce({
                data: {
                    count: 5,
                    count_filtered: 5,
                    rows: [{ id: 1 }]
                }
            } as any)
            .mockResolvedValueOnce({
                data: new Blob(['id,name\\n1,Alice'])
            } as any)

        const clickSpy = vi
            .spyOn(HTMLAnchorElement.prototype, 'click')
            .mockImplementation(() => undefined)

        const createObjectURL = vi.fn(() => 'blob:users')
        const revokeObjectURL = vi.fn()
        ;(window as any).URL.createObjectURL = createObjectURL
        ;(window as any).URL.revokeObjectURL = revokeObjectURL

        const sprunjer = useSprunjer('/api/users')
        await flushPromises()

        sprunjer.downloadCsv()
        await flushPromises()

        expect(getSpy).toHaveBeenNthCalledWith(2, '/api/users', {
            params: {
                size: 10,
                page: 0,
                sorts: {},
                filters: {},
                format: 'csv'
            },
            responseType: 'blob'
        })
        expect(createObjectURL).toHaveBeenCalledTimes(1)
        expect(clickSpy).toHaveBeenCalledTimes(1)
        expect(revokeObjectURL).toHaveBeenCalledWith('blob:users')
    })

    test('captures CSV download errors and resets loading', async () => {
        const getSpy = vi.spyOn(axios, 'get')
        getSpy
            .mockResolvedValueOnce({
                data: {
                    count: 0,
                    count_filtered: 0,
                    rows: []
                }
            } as any)
            .mockRejectedValueOnce({
                response: {
                    data: {
                        title: 'CSV failed',
                        description: 'Could not export CSV',
                        status: 500
                    }
                }
            })

        const sprunjer = useSprunjer('/api/users')
        await flushPromises()

        sprunjer.downloadCsv()
        await flushPromises()

        expect(sprunjer.error.value).toEqual({
            title: 'CSV failed',
            description: 'Could not export CSV',
            status: 500
        })
        expect(sprunjer.loading.value).toBe(false)
    })

    test('auto-fetches when reactive params or URL change', async () => {
        const getSpy = vi.spyOn(axios, 'get').mockResolvedValue({
            data: {
                count: 0,
                count_filtered: 0,
                rows: []
            }
        } as any)

        const dataUrl = ref('/api/first')
        const sprunjer = useSprunjer(() => dataUrl.value)

        await flushPromises()
        expect(getSpy).toHaveBeenCalledWith('/api/first', {
            params: {
                size: 10,
                page: 0,
                sorts: {},
                filters: {}
            }
        })

        sprunjer.page.value = 1
        await nextTick()
        await flushPromises()

        expect(getSpy).toHaveBeenLastCalledWith('/api/first', {
            params: {
                size: 10,
                page: 1,
                sorts: {},
                filters: {}
            }
        })

        dataUrl.value = '/api/second'
        await nextTick()
        await flushPromises()

        expect(getSpy).toHaveBeenLastCalledWith('/api/second', {
            params: {
                size: 10,
                page: 1,
                sorts: {},
                filters: {}
            }
        })
    })
})
