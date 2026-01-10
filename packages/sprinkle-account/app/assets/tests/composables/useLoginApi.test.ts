import { describe, expect, test, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useLoginApi } from '../../composables'
import type { LoginRequest } from '../../interfaces'

const form: LoginRequest = {
    user_name: '',
    password: '',
    rememberme: false
}

// Mock the auth store
const mockUseAuthStoreSetUser = vi.fn()
const mockUseAuthStoreUnsetUser = vi.fn()
vi.mock('../../stores', () => ({
    useAuthStore: () => ({
        setUser: mockUseAuthStoreSetUser,
        unsetUser: mockUseAuthStoreUnsetUser
    })
}))

// Mock CSRF Composable - Use partial mocking
const mockUseCsrfUpdateFromHeaders = vi.fn()
vi.mock('@userfrosting/sprinkle-core/composables', async () => {
    const actualModule = await vi.importActual('@userfrosting/sprinkle-core/composables')
    return {
        ...actualModule, // Keep all original exports
        useCsrf: () => ({
            updateFromHeaders: mockUseCsrfUpdateFromHeaders
        })
    }
})

// Mock composables
const mockUseAlertsStorePush = vi.fn()
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        translate: vi.fn().mockImplementation((key) => {
            return key
        })
    }),
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
    })
}))

describe('useLoginApi', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })
    test('should initialize formData with default values', () => {
        const { formData } = useLoginApi()
        expect(formData.value).toEqual({
            user_name: '',
            password: '',
            rememberme: false
        })
    })

    test('should expose r$ from useRegle', () => {
        const { r$ } = useLoginApi()
        expect(r$).toBeDefined()
    })

    test('should set apiError on failed login', async () => {
        const { submitLogin, apiError } = useLoginApi()
        const error = { response: { data: { description: 'Invalid credentials' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error as any)
        await expect(submitLogin(form)).rejects.toEqual({
            description: 'Invalid credentials',
            style: Severity.Danger
        })
        expect(apiError.value).toEqual({
            description: 'Invalid credentials',
            style: Severity.Danger
        })
    })

    test('should clear apiError before login', async () => {
        const { submitLogin, apiError } = useLoginApi()
        apiError.value = { description: 'Old error', style: Severity.Danger }
        const error = { response: { data: { description: 'Invalid credentials' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error as any)
        await expect(submitLogin(form)).rejects.toBeDefined()
        expect(apiError.value).toEqual({
            description: 'Invalid credentials',
            style: Severity.Danger
        })
    })

    test('should reset apiLoading after error', async () => {
        const { submitLogin, apiLoading } = useLoginApi()
        const error = { response: { data: { description: 'Invalid credentials' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error as any)
        expect(apiLoading.value).toBe(false)
        await expect(submitLogin(form)).rejects.toBeDefined()
        expect(apiLoading.value).toBe(false)
    })

    test('should call alerts, auth, and csrf composables on successful login', async () => {
        // Arrange
        const response = {
            data: {
                message: 'Login successful',
                user: { username: 'JaneDoe', email: 'jane.doe@example.com' }
            },
            headers: { 'csrf-token': 'csrf-token-value' }
        }
        const axiosPostSpy = vi.spyOn(axios, 'post').mockResolvedValue(response as any)

        // Act
        const { submitLogin } = useLoginApi()
        await submitLogin(form)

        // Assert
        expect(axiosPostSpy).toHaveBeenCalledWith('/account/login', form)
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Login successful',
            style: Severity.Success
        })
        expect(mockUseAuthStoreSetUser).toHaveBeenCalledWith(response.data.user)
        expect(mockUseCsrfUpdateFromHeaders).toHaveBeenCalledWith(response.headers)
    })

    test('should throw and set apiError if axios throws without response data', async () => {
        const { submitLogin, apiError } = useLoginApi()
        const error = new Error('Network Error')
        vi.spyOn(axios, 'post').mockRejectedValue(error)
        await expect(submitLogin(form)).rejects.toEqual({
            description: 'Network Error',
            style: Severity.Danger
        })
        expect(apiError.value).toEqual({
            description: 'Network Error',
            style: Severity.Danger
        })
    })

    test('should reset formData to default values using defaultFormData', () => {
        const { defaultFormData, formData } = useLoginApi()
        formData.value = {
            user_name: 'JohnDoe',
            password: 'password123',
            rememberme: true
        }
        formData.value = defaultFormData()
        expect(formData.value).toEqual({
            user_name: '',
            password: '',
            rememberme: false
        })
    })

    test('should set apiLoading true during request and false after', async () => {
        const { submitLogin, apiLoading } = useLoginApi()
        const response = {
            data: {
                message: 'Login successful',
                user: { username: 'JaneDoe', email: 'jane.doe@example.com' }
            },
            headers: {}
        }
        vi.spyOn(axios, 'post').mockImplementation(() => {
            expect(apiLoading.value).toBe(true)
            return Promise.resolve(response as any)
        })
        expect(apiLoading.value).toBe(false)
        await submitLogin(form)
        expect(apiLoading.value).toBe(false)
    })
})
