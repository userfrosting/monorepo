import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { useAxiosInterceptor } from '../../composables/useAxiosInterceptor'

const mockUnsetUser = vi.fn()
const mockFetchCsrfToken = vi.fn()

vi.mock('../../stores', () => ({
    useAuthStore: () => ({
        unsetUser: mockUnsetUser
    })
}))

vi.mock('@userfrosting/sprinkle-core/composables', async () => {
    const actualModule = await vi.importActual('@userfrosting/sprinkle-core/composables')

    return {
        ...actualModule,
        useCsrf: () => ({
            fetchCsrfToken: mockFetchCsrfToken
        })
    }
})

describe('useAxiosInterceptor', () => {
    const useSpy = vi.spyOn(axios.interceptors.response, 'use')

    beforeEach(() => {
        useSpy.mockClear()
    })

    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('registers response interceptor and returns successful responses unchanged', async () => {
        useAxiosInterceptor()

        expect(useSpy).toHaveBeenCalledTimes(1)

        const onFulfilled = useSpy.mock.calls[0][0] as (response: unknown) => unknown
        const response = { data: { ok: true } }

        expect(onFulfilled(response)).toBe(response)
    })

    test('handles 401 errors by unsetting user and refreshing CSRF token', async () => {
        useAxiosInterceptor()

        const onRejected = useSpy.mock.calls[0][1] as (error: unknown) => Promise<unknown>
        const error = {
            response: {
                status: 401
            }
        }

        await expect(onRejected(error)).rejects.toBe(error)

        expect(mockUnsetUser).toHaveBeenCalledTimes(1)
        expect(mockFetchCsrfToken).toHaveBeenCalledTimes(1)
    })

    test('does not unset user for non-401 errors', async () => {
        useAxiosInterceptor()

        const onRejected = useSpy.mock.calls[0][1] as (error: unknown) => Promise<unknown>
        const error = {
            response: {
                status: 500
            }
        }

        await expect(onRejected(error)).rejects.toBe(error)

        expect(mockUnsetUser).not.toHaveBeenCalled()
        expect(mockFetchCsrfToken).not.toHaveBeenCalled()
    })
})
