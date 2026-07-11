import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useUserEmailEditApi } from '../../composables/useUserEmailEditApi'

vi.mock('@regle/core', () => ({
    useRegle: () => ({ r$: {} })
}))

const mockUseAlertsStorePush = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
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

describe('useUserEmailEditApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('initializes formData and exposes validator', () => {
        const { formData, r$ } = useUserEmailEditApi()

        expect(formData.value).toEqual({
            email: '',
            passwordcheck: ''
        })
        expect(r$).toBeDefined()
    })

    test('submits email edit successfully and returns API response data', async () => {
        const { submitEmailEdit, apiLoading, apiError } = useUserEmailEditApi()
        const payload = {
            email: 'john@example.com',
            passwordcheck: 'secret'
        }
        const responseData = {
            title: 'Email Updated',
            description: 'Your email was changed'
        }

        vi.spyOn(axios, 'post').mockResolvedValue({ data: responseData } as any)

        await expect(submitEmailEdit(payload)).resolves.toEqual(responseData)
        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/settings/email', payload)
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            style: Severity.Success,
            ...responseData
        })
    })

    test('sets apiError and does not throw when email edit fails', async () => {
        const { submitEmailEdit, apiError, apiLoading } = useUserEmailEditApi()
        const payload = {
            email: 'john@example.com',
            passwordcheck: 'secret'
        }

        vi.spyOn(axios, 'post').mockRejectedValue({
            response: {
                data: {
                    title: 'Error',
                    description: 'Cannot update email'
                }
            }
        })

        await expect(submitEmailEdit(payload)).resolves.toBeUndefined()
        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toEqual({
            title: 'Error',
            description: 'Cannot update email'
        })
    })
})
