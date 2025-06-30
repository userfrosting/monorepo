import { beforeEach, describe, expect, test, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import axios from 'axios'
import { useAuthStore } from '../../stores/useAuthStore'
import type { UserDataInterface } from 'app/assets/interfaces'

const testUser: UserDataInterface = {
    id: 1,
    user_name: 'JohnDoe',
    first_name: 'John',
    last_name: 'Doe',
    full_name: 'John Doe',
    email: 'john.doe@example.com',
    avatar: '',
    flag_enabled: true,
    flag_verified: true,
    group_id: null,
    locale: 'en_US',
    created_at: '',
    updated_at: '',
    deleted_at: null,
    permissions: {
        'test.permission': ['always()']
    },
    is_master: false
}

// Mock useTranslator load function
const loadTranslator = vi.fn()
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        load: loadTranslator
    }),
    useConfigStore: () => ({
        get: vi.fn().mockReturnValue(false)
    })
}))

// Mock useCsrf updateFromHeaders function
const updateFromHeaders = vi.fn()
vi.mock('@userfrosting/sprinkle-core/composables', () => ({
    useCsrf: () => ({
        updateFromHeaders
    })
}))

describe('useAuthStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    test('should set, get and unset the user', () => {
        // Arrange
        const authStore = useAuthStore()

        // Assert initial state
        expect(authStore.user).toBeNull()
        expect(authStore.isAuthenticated).toBe(false)

        // Assert set user
        authStore.setUser(testUser)
        expect(authStore.user).toStrictEqual(testUser)
        expect(authStore.isAuthenticated).toBe(true)

        // Assert unset user
        authStore.unsetUser()
        expect(authStore.user).toBeNull()
        expect(authStore.isAuthenticated).toBe(false)
    })

    test('should check authentication successfully', async () => {
        // Arrange
        const authStore = useAuthStore()
        vi.spyOn(axios, 'get').mockResolvedValue({ data: testUser })

        // Assert initial state
        expect(authStore.user).toBeNull()

        // Act
        const result = await authStore.check()

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/account/auth-check')
        expect(result).toStrictEqual(testUser)
        expect(authStore.user).toStrictEqual(testUser)
    })

    test('should unset the user when authentication check fails', async () => {
        // Arrange
        const authStore = useAuthStore()
        const error = { response: { data: {} } }
        vi.spyOn(axios, 'get').mockRejectedValue({
            response: { status: 401, data: error.response.data }
        } as any)

        // Assert initial state
        authStore.setUser(testUser)
        expect(authStore.user).toStrictEqual(testUser)

        // Act & Assert
        await authStore.check()
        expect(axios.get).toHaveBeenCalledWith('/account/auth-check')
        expect(authStore.user).toBeNull()
    })

    // TODO : Update when 401 error handling is moved to the global error handler
    // test('should throw an error when authentication return anything other than a 401 status', async () => {
    //     // Arrange
    //     const authStore = useAuthStore()
    //     vi.spyOn(axios, 'get').mockRejectedValue({ response: { data: {} } } as any)

    //     // Assert initial state
    //     authStore.setUser(testUser)
    //     expect(authStore.user).toStrictEqual(testUser)

    //     // Act & Assert
    //     await expect(authStore.check()).rejects.toEqual({
    //         description: 'An error as occurred',
    //         style: Severity.Danger,
    //         closeBtn: true
    //     })
    //     expect(axios.get).toHaveBeenCalledWith('/account/auth-check')
    //     expect(authStore.user).toStrictEqual(testUser) // User is not nulled
    // })

    test('should check if the user has a permission', () => {
        // Arrange
        const authStore = useAuthStore()
        authStore.setUser(testUser)

        // Assert
        expect(authStore.checkAccess('test.permission')).toBe(true)
        expect(authStore.checkAccess('nonexistent.permission')).toBe(false)
    })
})
