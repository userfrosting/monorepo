import { setActivePinia, createPinia } from 'pinia'
import { beforeEach, describe, expect, test, vi } from 'vitest'
import { useAuthStore } from '../../stores/auth'
import axios from 'axios'
import type { LoginRequest, LoginResponse, UserInterface } from 'app/assets/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

const testUser: UserInterface = {
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
    deleted_at: null
}

const form: LoginRequest = {
    user_name: 'john',
    password: 'password'
}

// Mock useTranslator load function
const loadTranslator = vi.fn()
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        load: loadTranslator
    })
}))

describe('authStore', () => {
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

    test('should login successfully', async () => {
        // Arrange
        const authStore = useAuthStore()
        const response: LoginResponse = {
            user: testUser,
            message: 'Welcome back John Doe!',
            redirect: '/dashboard'
        }
        vi.spyOn(axios, 'post').mockResolvedValue({ data: response })

        // Assert initial state
        expect(authStore.user).toBeNull()

        // Act
        const result = await authStore.login(form)

        // Assert
        expect(axios.post).toHaveBeenCalledWith('/account/login', form)
        expect(result).toStrictEqual(response)
        expect(authStore.user).toStrictEqual(testUser)
        expect(loadTranslator).toHaveBeenCalled()
    })

    test('should throw an error when login fails', async () => {
        // Arrange
        const authStore = useAuthStore()
        const error = { response: { data: { description: 'Bad password' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error as any)

        // Assert initial state
        expect(authStore.user).toBeNull()

        // Act & Assert
        await expect(authStore.login(form)).rejects.toEqual({
            description: 'Bad password',
            style: Severity.Danger,
            closeBtn: true
        })
        expect(axios.post).toHaveBeenCalledWith('/account/login', form)
        expect(authStore.user).toBeNull()
    })

    test('should check authentication successfully', async () => {
        // Arrange
        const authStore = useAuthStore()
        const response = { data: { auth: true, user: testUser } }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

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
        vi.spyOn(axios, 'get').mockRejectedValue(error as any)

        // Assert initial state
        authStore.setUser(testUser)
        expect(authStore.user).toStrictEqual(testUser)

        // Act & Assert
        await expect(authStore.check()).rejects.toEqual({
            description: 'An error as occurred',
            style: Severity.Danger,
            closeBtn: true
        })
        expect(axios.get).toHaveBeenCalledWith('/account/auth-check')
        expect(authStore.user).toBeNull()
    })

    test('should logout successfully', async () => {
        // Arrange
        const authStore = useAuthStore()
        vi.spyOn(axios, 'get').mockResolvedValue({} as any)

        // Assert initial state
        authStore.setUser(testUser)
        expect(authStore.user).toStrictEqual(testUser)

        // Act
        await authStore.logout()

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/account/logout')
        expect(authStore.user).toBeNull()
        expect(loadTranslator).toHaveBeenCalled()
    })

    test('should throw an error when logout fails', async () => {
        // Arrange
        const authStore = useAuthStore()
        const error = { response: { data: {} } }
        vi.spyOn(axios, 'get').mockRejectedValue(error as any)

        // Assert initial state
        authStore.setUser(testUser)
        expect(authStore.user).toStrictEqual(testUser)

        // Act & Assert
        await expect(authStore.logout()).rejects.toEqual({
            description: 'An error as occurred',
            style: Severity.Danger,
            closeBtn: true
        })
        expect(axios.get).toHaveBeenCalledWith('/account/logout')
        expect(authStore.user).toBeNull() // User will be unset even if logout fails
    })
})
