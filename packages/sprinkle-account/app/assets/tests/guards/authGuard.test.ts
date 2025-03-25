// Unit tests for: useAuthGuard
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import { useAuthGuard } from '../../guards/authGuard'
import * as Auth from '../../stores/auth'
import type { RouteAuthGuard, RouteGuestGuard } from 'app/assets/interfaces'

// Default mock for the auth store and router
const mockAuthStore = {
    isAuthenticated: false,
    checkAccess: vi.fn()
}

const mockRouter = {
    currentRoute: {
        value: {
            path: '/foo/bar',
            meta: {
                auth: undefined as RouteAuthGuard | undefined,
                guest: undefined as RouteGuestGuard | undefined
            }
        }
    },
    replace: vi.fn()
}

describe('authGuard useAuthGuard() method', () => {
    beforeEach(() => {
        // Apply the mock to the auth store
        vi.spyOn(Auth, 'useAuthStore').mockReturnValue(mockAuthStore as any)
    })

    afterEach(() => {
        // Reset all mocks and "once" implementations
        vi.resetAllMocks()
    })

    test('should not do anything if both path are null (default)', () => {
        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).not.toHaveBeenCalled()
    })

    test('should redirect to login if route requires auth and user is not authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = { redirect: '/login' }
        mockAuthStore.isAuthenticated = false

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalled()
        expect(mockRouter.replace).toHaveBeenCalledWith('/login')
    })

    test('should not redirect if route requires auth and user is authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = { redirect: '/login' }
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).not.toHaveBeenCalled()
    })

    test('should not redirect if route requires permission and user does not have it', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = { permission: 'foo.bar', redirect: '/login' }
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith('/login')
    })

    test('should not redirect if route requires guests and user is not authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.guest = { redirect: '/' }
        mockAuthStore.isAuthenticated = false

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).not.toHaveBeenCalled()
    })

    test('should redirect to home if route is for guests and user is authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.guest = { redirect: '/' }
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith('/')
    })

    test('should use default redirect for auth guard if no redirect specified', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = {}
        mockAuthStore.isAuthenticated = false

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                name: 'Unauthorized'
            })
        )
    })

    test('should use default redirect for permission guard if no redirect specified', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = { permission: 'foo.bar' }
        mockAuthStore.isAuthenticated = false

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                name: 'Unauthorized'
            })
        )
    })

    test('should use default redirect for guest guard if no redirect specified', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.guest = {}
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                name: 'Unauthorized'
            })
        )
    })
})
