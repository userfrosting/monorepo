import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useForgotPasswordApi } from '../../composables/useForgotPasswordApi'

const mockUseAlertsStorePush = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
    })
}))

describe('useForgotPasswordApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('requestCode succeeds and pushes success alert', async () => {
        const { requestCode, apiLoading, apiError } = useForgotPasswordApi()
        vi.spyOn(axios, 'post').mockResolvedValue({
            data: {
                title: 'Request accepted',
                description: 'Verification code sent'
            }
        } as any)

        expect(apiLoading.value).toBe(false)
        await requestCode('john@example.com')

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/forgot-password/request', {
            email: 'john@example.com'
        })
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Request accepted',
            description: 'Verification code sent',
            style: Severity.Success
        })
    })

    test('requestCode throws and sets apiError when request fails', async () => {
        const { requestCode, apiLoading, apiError } = useForgotPasswordApi()
        vi.spyOn(axios, 'post').mockRejectedValue({
            response: {
                data: {
                    title: 'Error',
                    description: 'Cannot request reset'
                }
            }
        })

        await expect(requestCode('john@example.com')).rejects.toEqual({
            title: 'Error',
            description: 'Cannot request reset'
        })

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toEqual({
            title: 'Error',
            description: 'Cannot request reset'
        })
    })

    test('setPassword succeeds and pushes success alert', async () => {
        const { setPassword, apiLoading, apiError } = useForgotPasswordApi()
        const payload = {
            email: 'john@example.com',
            token: 'ABC123',
            password: 'secret',
            passwordc: 'secret'
        }

        vi.spyOn(axios, 'post').mockResolvedValue({
            data: {
                title: 'Password Updated',
                description: 'Password was changed'
            }
        } as any)

        await setPassword(payload as any)

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/forgot-password/set-password', payload)
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Password Updated',
            description: 'Password was changed',
            style: Severity.Success
        })
    })

    test('setPassword throws and sets apiError when request fails', async () => {
        const { setPassword, apiError } = useForgotPasswordApi()
        const payload = {
            email: 'john@example.com',
            token: 'ABC123',
            password: 'secret',
            passwordc: 'secret'
        }

        vi.spyOn(axios, 'post').mockRejectedValue({
            response: {
                data: {
                    title: 'Error',
                    description: 'Cannot set password'
                }
            }
        })

        await expect(setPassword(payload as any)).rejects.toEqual({
            title: 'Error',
            description: 'Cannot set password'
        })

        expect(apiError.value).toEqual({
            title: 'Error',
            description: 'Cannot set password'
        })
    })
})
