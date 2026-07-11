import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useEmailVerificationApi } from '../../composables/useEmailVerificationApi'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

// Mock the alert store
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseAlertsStore = {
    push: vi.fn()
}

describe('useEmailVerificationApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('should successfully resend verification email', async () => {
        const { requestVerificationCode, apiLoading, apiError } = useEmailVerificationApi()
        const email = 'test@example.com'
        const successMessage = 'Verification email sent successfully'

        // Set axios mock
        vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { title: successMessage } })

        // Act
        const result = await requestVerificationCode(email)

        // Assert
        expect(result).toEqual({
            title: successMessage
        })
        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/verify/request', { email })
    })

    test('should handle error when resending verification email fails', async () => {
        const { requestVerificationCode, apiLoading, apiError } = useEmailVerificationApi()
        const email = 'test@example.com'
        const errorResponse = {
            response: {
                data: {
                    title: 'Verification Exception',
                    description:
                        'This verification code is not valid, or the account is already verified.',
                    status: '400'
                }
            }
        }
        const expectedError = {
            description: 'This verification code is not valid, or the account is already verified.',
            status: '400',
            title: 'Verification Exception'
        }

        // Set axios mock
        vi.spyOn(axios, 'post').mockRejectedValueOnce(errorResponse)

        // Act & Assert
        await requestVerificationCode(email)
        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toEqual(expectedError)
    })

    test('should set fallback error when resending verification email fails without response', async () => {
        const { requestVerificationCode, apiError } = useEmailVerificationApi()
        vi.spyOn(axios, 'post').mockRejectedValueOnce(new Error('Network Error'))

        await requestVerificationCode('test@example.com')

        expect(apiError.value).toEqual({ description: 'Network Error' })
    })

    test('should successfully submit verification code', async () => {
        const { submitVerificationCode, apiLoading, apiError } = useEmailVerificationApi()
        const email = 'test@example.com'
        const code = '123456'
        const successMessage = 'Verification successful'

        // Set mocks
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)
        vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { message: successMessage } })

        // Act & Assert
        const result = await submitVerificationCode(email, code)
        expect(apiError.value).toBeNull()
        expect(result).toEqual({ message: successMessage })
        expect(apiLoading.value).toBe(false)
        expect(axios.post).toHaveBeenCalledWith('/account/verify/email', { email, code })
    })

    test('should handle error when submitting verification code fails', async () => {
        const { submitVerificationCode, apiLoading, apiError } = useEmailVerificationApi()
        const email = 'test@example.com'
        const code = '123456'
        const errorResponse = {
            response: {
                data: {
                    description: 'Invalid verification code',
                    style: Severity.Danger,
                    closeBtn: true
                }
            }
        }

        // Act & Assert
        vi.spyOn(axios, 'post').mockRejectedValueOnce(errorResponse)

        // Act & Assert
        await submitVerificationCode(email, code)
        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toEqual({
            description: 'Invalid verification code',
            style: Severity.Danger,
            closeBtn: true
        })
    })

    test('should set fallback error when submitting verification code fails without response', async () => {
        const { submitVerificationCode, apiError } = useEmailVerificationApi()
        vi.spyOn(axios, 'post').mockRejectedValueOnce(new Error('Network Error'))

        await submitVerificationCode('test@example.com', '123456')

        expect(apiError.value).toEqual({ description: 'Network Error' })
    })

    test('should set loading state to true', async () => {
        const { submitVerificationCode, apiLoading } = useEmailVerificationApi()
        const email = 'test@example.com'
        const code = '123456'
        const successMessage = 'Verification successful'

        // Set mocks
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)
        vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { message: successMessage } })

        // Act & Assert
        expect(apiLoading.value).toBe(false)
        const submitPromise = submitVerificationCode(email, code)
        expect(apiLoading.value).toBe(true)
        await submitPromise
        expect(apiLoading.value).toBe(false)
    })
})
