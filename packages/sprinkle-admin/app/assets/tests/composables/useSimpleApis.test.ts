import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { createPinia, setActivePinia } from 'pinia'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import {
    useConfigCacheApi,
    useConfigSystemInfoApi,
    useDashboardApi,
    useGroupsApi,
    usePermissionApi,
    useRolePermissionsApi,
    useRoleUpdateApi,
    useUserPasswordResetApi,
    useUserRolesApi,
    useUserUpdateApi
} from '../../composables'

const mockPush = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        push: mockPush
    })
}))

describe('simple admin composables', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('useDashboardApi load sets default state, resolves success and swallows error', async () => {
        const dashboardApi = useDashboardApi()

        expect(dashboardApi.data).toEqual({
            counter: { users: 0, roles: 0, groups: 0 },
            users: []
        })

        vi.spyOn(axios, 'get').mockResolvedValueOnce({
            data: { counter: { users: 2, roles: 3, groups: 4 }, users: [{ id: 1 }] }
        } as any)

        await expect(dashboardApi.load()).resolves.toEqual({
            counter: { users: 2, roles: 3, groups: 4 },
            users: [{ id: 1 }]
        })

        vi.spyOn(axios, 'get').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        await expect(dashboardApi.load()).resolves.toBeUndefined()
    })

    test('useConfigCacheApi clearCache handles success and error', async () => {
        const { clearCache, error, loading } = useConfigCacheApi()

        vi.spyOn(axios, 'post').mockResolvedValueOnce({
            data: { title: 'Cache cleared', description: 'done' }
        } as any)

        await expect(clearCache()).resolves.toEqual({
            title: 'Cache cleared',
            description: 'done'
        })

        expect(mockPush).toHaveBeenCalledWith({
            title: 'Cache cleared',
            description: 'done',
            style: Severity.Success
        })
        expect(error.value).toBeNull()
        expect(loading.value).toBe(false)

        vi.spyOn(axios, 'post').mockRejectedValueOnce({
            response: { data: { title: 'Nope' } }
        })

        await clearCache()
        expect(error.value).toEqual({ title: 'Nope' })
        expect(loading.value).toBe(false)
    })

    test('useConfigSystemInfoApi load handles success and error', async () => {
        const { load, data, error, loading } = useConfigSystemInfoApi()

        vi.spyOn(axios, 'get').mockResolvedValueOnce({
            data: {
                frameworkVersion: '6.0',
                phpVersion: '8.4',
                database: { connection: 'mysql', name: 'db', type: 'mysql', version: '8' },
                server: 'localhost',
                projectPath: '/app',
                sprinkles: { core: 'x' }
            }
        } as any)

        await load()

        expect(data.value.frameworkVersion).toBe('6.0')
        expect(error.value).toBeNull()
        expect(loading.value).toBe(false)

        vi.spyOn(axios, 'get').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        await load()
        expect(error.value).toEqual({ title: 'Error' })
    })

    test('useGroupsApi updateGroups handles success and error', async () => {
        const { updateGroups, groups, error, loading } = useGroupsApi()

        vi.spyOn(axios, 'get').mockResolvedValueOnce({
            data: { rows: [{ id: 1, slug: 'a' }] }
        } as any)

        await updateGroups()
        expect(groups.value).toEqual([{ id: 1, slug: 'a' }])
        expect(error.value).toBeNull()
        expect(loading.value).toBe(false)

        vi.spyOn(axios, 'get').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        await updateGroups()
        expect(error.value).toEqual({ title: 'Error' })
    })

    test('usePermissionApi auto-fetches and handles success and error', async () => {
        vi.spyOn(axios, 'get').mockResolvedValueOnce({
            data: {
                id: 10,
                slug: 'perm.slug',
                name: 'Permission',
                conditions: '',
                description: '',
                created_at: '',
                updated_at: '',
                deleted_at: null
            }
        } as any)

        const { permission, fetchPermission, error, loading } = usePermissionApi(10)
        await vi.waitFor(() => {
            expect(permission.value.id).toBe(10)
        })

        expect(error.value).toBeNull()
        await vi.waitFor(() => {
            expect(loading.value).toBe(false)
        })

        vi.spyOn(axios, 'get').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        await fetchPermission()
        expect(error.value).toEqual({ title: 'Error' })
    })

    test('useRolePermissionsApi fetch handles success and error', async () => {
        const { fetch, selected, error, loading } = useRolePermissionsApi()

        vi.spyOn(axios, 'get').mockResolvedValueOnce({
            data: { rows: [{ id: 1 }, { id: 3 }] }
        } as any)

        await fetch('admin')
        expect(selected.value).toEqual([1, 3])
        expect(error.value).toBeUndefined()
        expect(loading.value).toBe(false)

        vi.spyOn(axios, 'get').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        fetch('admin')
        await vi.waitFor(() => {
            expect(error.value).toEqual({ title: 'Error' })
        })
    })

    test('useUserRolesApi fetch handles success and error', async () => {
        const { fetch, selected, error, loading } = useUserRolesApi()

        vi.spyOn(axios, 'get').mockResolvedValueOnce({
            data: { rows: [{ id: 2 }, { id: 7 }] }
        } as any)

        await fetch('alice')
        expect(selected.value).toEqual([2, 7])
        expect(error.value).toBeUndefined()
        expect(loading.value).toBe(false)

        vi.spyOn(axios, 'get').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        fetch('alice')
        await vi.waitFor(() => {
            expect(error.value).toEqual({ title: 'Error' })
        })
    })

    test('useRoleUpdateApi submitRoleUpdate handles success and error', async () => {
        const { submitRoleUpdate, apiError, apiLoading } = useRoleUpdateApi()

        vi.spyOn(axios, 'put').mockResolvedValueOnce({
            data: { title: 'Updated', description: 'ok' }
        } as any)

        await expect(submitRoleUpdate('admin', 'name', { name: 'Admin' })).resolves.toEqual({
            title: 'Updated',
            description: 'ok'
        })

        expect(mockPush).toHaveBeenCalledWith({
            style: Severity.Success,
            title: 'Updated',
            description: 'ok'
        })
        expect(apiError.value).toBeNull()
        expect(apiLoading.value).toBe(false)

        vi.spyOn(axios, 'put').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        await expect(submitRoleUpdate('admin', 'name', { name: 'Admin' })).resolves.toBeUndefined()
        expect(apiError.value).toEqual({ title: 'Error' })
    })

    test('useUserUpdateApi submitUserUpdate handles success and error', async () => {
        const { submitUserUpdate, apiError, apiLoading } = useUserUpdateApi()

        vi.spyOn(axios, 'put').mockResolvedValueOnce({
            data: { title: 'Updated', description: 'ok' }
        } as any)

        await expect(submitUserUpdate('alice', 'name', { first_name: 'Alice' })).resolves.toEqual({
            title: 'Updated',
            description: 'ok'
        })

        expect(mockPush).toHaveBeenCalledWith({
            style: Severity.Success,
            title: 'Updated',
            description: 'ok'
        })
        expect(apiError.value).toBeNull()
        expect(apiLoading.value).toBe(false)

        vi.spyOn(axios, 'put').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })
        await expect(
            submitUserUpdate('alice', 'name', { first_name: 'Alice' })
        ).resolves.toBeUndefined()
        expect(apiError.value).toEqual({ title: 'Error' })
    })

    test('useUserPasswordResetApi passwordReset handles success and error', async () => {
        const { passwordReset, apiError, apiLoading } = useUserPasswordResetApi()

        vi.spyOn(axios, 'post').mockResolvedValueOnce({
            data: { title: 'Reset', description: 'ok' }
        } as any)

        await passwordReset('alice')
        expect(mockPush).toHaveBeenCalledWith({
            title: 'Reset',
            description: 'ok',
            style: Severity.Success
        })
        expect(apiError.value).toBeNull()
        expect(apiLoading.value).toBe(false)

        vi.spyOn(axios, 'post').mockRejectedValueOnce({ response: { data: { title: 'Error' } } })

        await expect(passwordReset('alice')).rejects.toEqual({ title: 'Error' })
        expect(apiError.value).toEqual({ title: 'Error' })
    })
})
