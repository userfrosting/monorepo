// Unit tests for: useAuthGuard
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import { useAuthGuard } from '../../guards/authGuard'
import * as Auth from '../../stores/useAuthStore'
import type { RouteGuard, RoutePermissionGuard } from '../../interfaces'

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
                auth: undefined as RouteGuard | undefined,
                guest: undefined as RouteGuard | undefined,
                permission: undefined as RoutePermissionGuard | undefined
            },
            query: {} as Record<string, string>
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
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = undefined
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
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = undefined
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).not.toHaveBeenCalled()
    })

    test('should not redirect if route requires permission and user does not have it', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = { slug: 'foo.bar', redirect: '/login' }
        mockRouter.currentRoute.value.meta.guest = undefined
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith('/login')
    })

    test('should not redirect if route requires guests and user is not authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = { redirect: '/' }
        mockAuthStore.isAuthenticated = false

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).not.toHaveBeenCalled()
    })

    test('should redirect to specified redirect if route is for guests and user is authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = { redirect: '/' }
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                path: '/'
            })
        )
    })

    test('should redirect to specified redirect if route is for guests and user is authenticated (with route name)', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = { redirect: { name: 'home' } }
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                name: 'home'
            })
        )
    })

    test('should redirect to query redirect if route is for guests and user is authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = {}
        mockAuthStore.isAuthenticated = true

        // Set the query parameter
        mockRouter.currentRoute.value.query = {
            redirect: '/foo/bar'
        }

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                path: '/foo/bar'
            })
        )
    })

    test('should redirect to error page if route is for guests and user is authenticated', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = undefined
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

    test('should use default redirect for auth guard if no redirect specified', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = {}
        mockRouter.currentRoute.value.meta.permission = undefined
        mockRouter.currentRoute.value.meta.guest = undefined
        mockAuthStore.isAuthenticated = false

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                name: 'account.login'
            })
        )
    })

    test('should use default redirect for permission guard if no redirect specified', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = { slug: 'foo.bar' }
        mockRouter.currentRoute.value.meta.guest = undefined
        mockAuthStore.isAuthenticated = true

        // Act
        useAuthGuard(mockRouter as any)

        // Assert
        expect(mockRouter.replace).toHaveBeenCalledWith(
            expect.objectContaining({
                name: 'Forbidden'
            })
        )
    })

    test('should use default redirect for guest guard if no redirect specified', () => {
        // Arrange
        mockRouter.currentRoute.value.meta.auth = undefined
        mockRouter.currentRoute.value.meta.permission = undefined
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
