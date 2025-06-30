import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useCsrf } from '@userfrosting/sprinkle-core/composables'
import { useLogoutApi } from '../../composables'
import { useAuthStore } from '../../stores'

const { submitLogout, apiLoading } = useLogoutApi()

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

const response = {
    data: {
        description: 'Logout successful'
    },
    headers: { 'csrf-token': 'csrf-token-value' }
}

describe('useLoginApi', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('should logout successfully', async () => {
        // Arrange
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)
        vi.mocked(useCsrf).mockReturnValue(mockUseCsrf as any)
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

        // Act
        await submitLogout()

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/account/logout')
        expect(mockUseAlertsStore.push).toHaveBeenCalledWith({
            description: 'Logout successful',
            style: Severity.Success
        })
        expect(mockUseAuthStore.unsetUser).toHaveBeenCalled()
        expect(mockUseCsrf.updateFromHeaders).toHaveBeenCalledWith(response.headers)
    })

    test('should throw an error when logout fails', async () => {
        // Arrange
        const error = { response: { data: { description: 'Logout failed' } } }
        vi.spyOn(axios, 'get').mockRejectedValue(error as any)

        // Act & Assert
        await expect(submitLogout()).rejects.toEqual({
            description: 'Logout failed',
            style: Severity.Danger
        })
        expect(axios.get).toHaveBeenCalledWith('/account/logout')
    })

    test('should throw an error when logout fails due to a non api related cause', async () => {
        // Arrange
        const error = { response: { data: { description: 'Logout successful' } } }
        vi.spyOn(axios, 'get').mockResolvedValue(error as any)

        // Act & Assert
        // useAlertsStore is not defined on purpose to simulate it failing
        await expect(submitLogout()).rejects.toEqual({
            description: "Cannot read properties of undefined (reading 'push')",
            style: Severity.Danger
        })
        expect(axios.get).toHaveBeenCalledWith('/account/logout')
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
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

        // Act
        expect(apiLoading.value).toBe(false)
        const submitPromise = submitLogout()
        expect(apiLoading.value).toBe(true)
        await submitPromise
        expect(apiLoading.value).toBe(false)
    })
})
