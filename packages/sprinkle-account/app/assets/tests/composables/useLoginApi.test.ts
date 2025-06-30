import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useLoginApi } from '../../composables'
import { useAuthStore } from '../../stores'
import { useCsrf } from '@userfrosting/sprinkle-core/composables'
import type { LoginRequest } from '../../interfaces'

const { submitLogin, defaultFormData, apiLoading } = useLoginApi()

const form: LoginRequest = {
    user_name: '',
    password: '',
    rememberme: false
}

// Mock the config & alert stores
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseAlertsStore = {
    push: vi.fn()
}

vi.mock('../../stores')
const mockUseAuthStore = {
    setUser: vi.fn(),
    unsetUser: vi.fn()
}

vi.mock('@userfrosting/sprinkle-core/composables')
const mockUseCsrf = {
    updateFromHeaders: vi.fn()
}

describe('useLoginApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('should return default form', () => {
        // Act
        const result = defaultFormData()

        // Assert
        expect(result).toEqual({
            user_name: '',
            password: '',
            rememberme: false
        })
    })

    test('should login successfully', async () => {
        // Arrange
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)
        vi.mocked(useCsrf).mockReturnValue(mockUseCsrf as any)
        const response = {
            data: {
                message: 'Login successful',
                user: { username: 'JohnDoe', email: 'john.doe@example.com' }
            },
            headers: { 'csrf-token': 'csrf-token-value' }
        }
        vi.spyOn(axios, 'post').mockResolvedValue(response as any)

        // Act
        await submitLogin(form)

        // Assert
        expect(axios.post).toHaveBeenCalledWith('/account/login', form)
        expect(mockUseAlertsStore.push).toHaveBeenCalledWith({
            title: 'Login successful',
            style: Severity.Success
        })
        expect(mockUseAuthStore.setUser).toHaveBeenCalledWith(response.data.user)
        expect(mockUseCsrf.updateFromHeaders).toHaveBeenCalledWith(response.headers)
    })

    test('should throw an error when login fails', async () => {
        // Arrange
        const error = { response: { data: { description: 'Login failed' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error as any)

        // Act & Assert
        await expect(submitLogin(form)).rejects.toEqual({
            description: 'Login failed',
            style: Severity.Danger
        })
        expect(axios.post).toHaveBeenCalledWith('/account/login', form)
    })

    test('should throw an error when login fails due to a non api related cause', async () => {
        // Arrange
        const error = { response: { data: { description: 'Login successful' } } }
        vi.spyOn(axios, 'post').mockResolvedValue(error as any)

        // Act & Assert
        // useAlertsStore is not defined on purpose to simulate it failing
        await expect(submitLogin(form)).rejects.toEqual({
            description: "Cannot read properties of undefined (reading 'push')",
            style: Severity.Danger
        })
        expect(axios.post).toHaveBeenCalledWith('/account/login', form)
    })

    test('should set loading state to true', async () => {
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)
        vi.mocked(useCsrf).mockReturnValue(mockUseCsrf as any)
        const response = {
            data: {
                message: 'Login successful',
                user: { username: 'JohnDoe', email: 'john.doe@example.com' }
            },
            headers: { 'csrf-token': 'csrf-token-value' }
        }
        vi.spyOn(axios, 'post').mockResolvedValue(response as any)

        // Act
        expect(apiLoading.value).toBe(false)
        const submitPromise = submitLogin(form)
        expect(apiLoading.value).toBe(true)
        await submitPromise
        expect(apiLoading.value).toBe(false)
    })
})
