import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useAuthorizationManager } from '../../composables'
import type { UserDataInterface } from '../../interfaces'

// Mock the user and it's permissions
const mockUser: UserDataInterface = {
    id: 1,
    user_name: 'test_user',
    first_name: 'Test',
    last_name: 'User',
    full_name: 'Test User',
    email: 'test_user@example.com',
    avatar: '',
    flag_enabled: true,
    flag_verified: true,
    group_id: null,
    locale: 'en_US',
    created_at: new Date().toString(),
    updated_at: new Date().toString(),
    deleted_at: null,
    is_master: false,
    permissions: {
        'test.permission': ['always()'],
        'unsupported.permission': ['unsupportedCondition()'],
        'multiple.conditions': ['always()', 'unsupportedCondition()']
    }
}

// Mock the config store
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseConfigStore = {
    get: vi.fn()
}

describe('useAuthorizationManager', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    beforeEach(() => {
        // Mock the config store
        mockUseConfigStore.get.mockReturnValue(true)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        // Mock console.debug
        vi.spyOn(console, 'debug').mockImplementation(() => {})
    })

    test('should deny access if no user is defined', () => {
        const { checkAccess } = useAuthorizationManager(null)
        expect(checkAccess('test.permission')).toBe(false)
        expect(console.debug).toHaveBeenCalledWith('No user defined. Access denied.', undefined)
    })

    test('should deny access if no permissions are defined', () => {
        // Force mock user permissions to be empty
        const mockUserWithNoPermissions = { ...mockUser, permissions: {} }
        const { checkAccess } = useAuthorizationManager(mockUserWithNoPermissions)
        expect(checkAccess('test.permission')).toBe(false)
    })

    test('should deny access if the permission slug is not found', () => {
        const { checkAccess } = useAuthorizationManager(mockUser)
        expect(checkAccess('nonexistent.permission')).toBe(false)
    })

    test('should grant access if the condition is "always()"', () => {
        const { checkAccess } = useAuthorizationManager(mockUser)
        expect(checkAccess('test.permission')).toBe(true)
    })

    test('should grant access if the user is the master user', () => {
        // Force mock user permissions to be empty
        const mockUserIsMaster = { ...mockUser, is_master: true }
        const { checkAccess } = useAuthorizationManager(mockUserIsMaster)

        // Accept even if the condition is unsupported or doesn't exist
        expect(checkAccess('test.permission')).toBe(true)
        expect(checkAccess('unsupported.permission')).toBe(true)
        expect(checkAccess('exist.not')).toBe(true)
    })

    test('should deny access if the condition is unsupported', () => {
        const { checkAccess } = useAuthorizationManager(mockUser)
        expect(checkAccess('unsupported.permission')).toBe(false)
    })

    test('should grant access if conditions contains one "always()"', () => {
        const { checkAccess } = useAuthorizationManager(mockUser)
        expect(checkAccess('multiple.conditions')).toBe(true)
    })

    test('should deny access if no conditions are met', () => {
        const { checkAccess } = useAuthorizationManager(mockUser)
        expect(checkAccess('exist.not')).toBe(false)
    })

    test('should not send debug to console if config disabled', () => {
        // Disable debug for this test
        mockUseConfigStore.get.mockReturnValue(false)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const { checkAccess } = useAuthorizationManager(null)
        expect(checkAccess('test.permission')).toBe(false)
        expect(console.debug).not.toHaveBeenCalled()
    })
})
