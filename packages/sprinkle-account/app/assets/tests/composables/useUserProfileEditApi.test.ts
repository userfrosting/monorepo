import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useUserProfileEditApi } from '../../composables/useUserProfileEditApi'

vi.mock('@regle/core', () => ({
    useRegle: () => ({ r$: {} })
}))

const mockUseAlertsStorePush = vi.fn()
const mockUseTranslatorLoad = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
    }),
    useTranslator: () => ({
        load: mockUseTranslatorLoad
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

describe('useUserProfileEditApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('initializes formData and validator', () => {
        const { formData, r$ } = useUserProfileEditApi()

        expect(formData.value).toEqual({
            first_name: '',
            last_name: '',
            locale: ''
        })
        expect(r$).toBeDefined()
    })

    test('submits profile edit successfully and reloads translator', async () => {
        const { submitProfileEdit, apiLoading, apiError } = useUserProfileEditApi()
        const payload = {
            first_name: 'John',
            last_name: 'Doe',
            locale: 'en_US'
        }

        vi.spyOn(axios, 'post').mockResolvedValue({
            data: {
                title: 'Profile updated',
                description: 'Saved'
            }
        } as any)

        await submitProfileEdit(payload)

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/settings/profile', payload)
        expect(mockUseTranslatorLoad).toHaveBeenCalled()
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Profile updated',
            description: 'Saved',
            style: Severity.Success
        })
    })

    test('throws and sets apiError when profile edit fails', async () => {
        const { submitProfileEdit, apiError, apiLoading } = useUserProfileEditApi()
        const payload = {
            first_name: 'John',
            last_name: 'Doe',
            locale: 'en_US'
        }

        vi.spyOn(axios, 'post').mockRejectedValue({
            response: {
                data: {
                    title: 'Error',
                    description: 'Cannot update profile'
                }
            }
        })

        await expect(submitProfileEdit(payload)).rejects.toEqual({
            title: 'Error',
            description: 'Cannot update profile'
        })

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toEqual({
            title: 'Error',
            description: 'Cannot update profile'
        })
    })
})
