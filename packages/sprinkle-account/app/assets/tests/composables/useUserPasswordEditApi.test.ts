import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useUserPasswordEditApi } from '../../composables/useUserPasswordEditApi'

vi.mock('@regle/core', () => ({
    useRegle: () => ({ r$: {} })
}))

const mockUseAlertsStorePush = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
    }),
    useConfigStore: () => ({
        get: vi.fn().mockImplementation((key: string) => {
            if (key === 'site.password.length.min') return 8
            if (key === 'site.password.length.max') return 64
            return null
        })
    })
}))

vi.mock('@userfrosting/sprinkle-core/composables', async () => {
    const actualModule = await vi.importActual('@userfrosting/sprinkle-core/composables')

    return {
        ...actualModule,
        useRuleSchemaAdapter: () => ({
            adapt: vi.fn().mockReturnValue({})
        })
    }
})

describe('useUserPasswordEditApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('initializes min and max password length from config', () => {
        const { minLength, maxLength } = useUserPasswordEditApi()

        expect(minLength.value).toBe(8)
        expect(maxLength.value).toBe(64)
    })

    test('submits password edit successfully', async () => {
        const { submitPasswordEdit, apiLoading, apiError } = useUserPasswordEditApi()
        const payload = {
            passwordcheck: 'old-secret',
            password: 'new-secret',
            passwordc: 'new-secret'
        }

        vi.spyOn(axios, 'post').mockResolvedValue({
            data: {
                title: 'Password Updated',
                description: 'Password changed successfully'
            }
        } as any)

        await submitPasswordEdit(payload)

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/settings', payload)
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Password Updated',
            description: 'Password changed successfully',
            style: Severity.Success
        })
    })

    test('throws and sets apiError when password edit fails', async () => {
        const { submitPasswordEdit, apiError, apiLoading } = useUserPasswordEditApi()
        const payload = {
            passwordcheck: 'old-secret',
            password: 'new-secret',
            passwordc: 'new-secret'
        }

        vi.spyOn(axios, 'post').mockRejectedValue({
            response: {
                data: {
                    title: 'Error',
                    description: 'Cannot update password'
                }
            }
        })

        await expect(submitPasswordEdit(payload)).rejects.toEqual({
            title: 'Error',
            description: 'Cannot update password'
        })

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toEqual({
            title: 'Error',
            description: 'Cannot update password'
        })
    })
})
