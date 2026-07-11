import { beforeEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { useAxiosInterceptor } from '../../composables/useAxiosInterceptor'
import { Severity } from '../../interfaces'

const push = vi.fn()

beforeEach(() => {
    push.mockReset()
})

vi.mock('../../stores/useAlertsStore', () => ({
    useAlertsStore: () => ({
        push
    })
}))

describe('useAxiosInterceptor', () => {
    test('registers interceptor and passes successful responses through', () => {
        let onSuccess: ((response: any) => any) | undefined

        vi.spyOn(axios.interceptors.response, 'use').mockImplementation((success: any) => {
            onSuccess = success
            return 1
        })

        useAxiosInterceptor()

        expect(axios.interceptors.response.use).toHaveBeenCalledTimes(1)
        expect(onSuccess).toBeTypeOf('function')

        const response = { data: { ok: true } }
        expect(onSuccess?.(response)).toBe(response)
    })

    test('pushes danger alert for non-401 errors and rejects', async () => {
        let onError: ((error: any) => Promise<never>) | undefined

        vi.spyOn(axios.interceptors.response, 'use').mockImplementation((_: any, error: any) => {
            onError = error
            return 1
        })

        useAxiosInterceptor()

        const error = {
            message: 'Request failed',
            response: {
                status: 500,
                statusText: 'Internal Server Error',
                data: {
                    title: 'Server Error',
                    description: 'Something went wrong'
                }
            }
        }

        await expect(onError?.(error)).rejects.toBe(error)

        expect(push).toHaveBeenCalledWith({
            title: 'Server Error',
            description: 'Something went wrong',
            style: Severity.Danger
        })
    })

    test('uses fallback alert fields when API payload is incomplete', async () => {
        let onError: ((error: any) => Promise<never>) | undefined

        vi.spyOn(axios.interceptors.response, 'use').mockImplementation((_: any, error: any) => {
            onError = error
            return 1
        })

        useAxiosInterceptor()

        const error = {
            message: 'Fallback message',
            response: {
                status: 500,
                statusText: 'Internal Error',
                data: {}
            }
        }

        await expect(onError?.(error)).rejects.toBe(error)

        expect(push).toHaveBeenCalledWith({
            title: 'Internal Error',
            description: 'Fallback message',
            style: Severity.Danger
        })
    })

    test('ignores 401 errors for alerts but still rejects', async () => {
        let onError: ((error: any) => Promise<never>) | undefined

        vi.spyOn(axios.interceptors.response, 'use').mockImplementation((_: any, error: any) => {
            onError = error
            return 1
        })

        useAxiosInterceptor()

        const error = {
            message: 'Unauthorized',
            response: {
                status: 401,
                statusText: 'Unauthorized',
                data: {
                    title: 'Unauthorized',
                    description: 'Please log in'
                }
            }
        }

        await expect(onError?.(error)).rejects.toBe(error)
        expect(push).not.toHaveBeenCalled()
    })
})
