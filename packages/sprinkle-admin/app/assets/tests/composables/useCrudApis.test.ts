import { afterEach, describe, expect, test, vi } from 'vitest'
import { nextTick } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useGroupApi, useRoleApi, useUserApi } from '../../composables'

vi.mock('@regle/core', () => ({
    useRegle: () => ({ r$: {} })
}))

const mockPush = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        push: mockPush
    })
}))

vi.mock('@userfrosting/sprinkle-core/composables', async () => {
    const actualModule = await vi.importActual('@userfrosting/sprinkle-core/composables')

    return {
        ...actualModule,
        useRuleSchemaAdapter: () => ({
            adapt: vi.fn().mockReturnValue({})
        })
    }
})

describe('CRUD admin composables', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('useGroupApi handles slug watch, reset, and all CRUD methods', async () => {
        const api = useGroupApi()

        expect(api.formData.value).toEqual({
            slug: '',
            name: '',
            description: '',
            icon: 'users'
        })

        api.formData.value.name = 'My Group'
        await nextTick()
        expect(api.formData.value.slug).toBe('my-group')
        expect(api.slugLocked.value).toBe(true)

        api.formData.value.slug = 'custom-team'
        api.formData.value.name = 'Custom Group'
        await nextTick()
        expect(api.formData.value.slug).toBe('custom-team')
        expect(api.slugLocked.value).toBe(false)

        api.formData.value.name = 'Renamed Custom Group'
        await nextTick()
        expect(api.formData.value.slug).toBe('custom-team')

        api.slugLocked.value = true
        api.formData.value.slug = 'renamed-custom-group'
        await nextTick()
        expect(api.slugLocked.value).toBe(true)

        api.formData.value.name = 'Group With Generated Slug'
        await nextTick()
        expect(api.formData.value.slug).toBe('group-with-generated-slug')

        api.slugLocked.value = false
        api.formData.value.name = 'Name Change While Unlocked'
        await nextTick()
        expect(api.formData.value.slug).toBe('group-with-generated-slug')

        api.formData.value.slug = 'custom-team'
        await nextTick()
        api.slugLocked.value = true
        api.formData.value.name = 'Renamed Custom Group Again'
        await nextTick()
        expect(api.formData.value.slug).toBe('custom-team')
        expect(api.slugLocked.value).toBe(false)

        vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: { slug: 'admins' } } as any)
        await expect(api.fetchGroup('admins')).resolves.toEqual({ slug: 'admins' })

        vi.spyOn(axios, 'post').mockResolvedValueOnce({
            data: { title: 'Created', description: 'ok' }
        } as any)
        await api.createGroup({ slug: 'new', name: 'New', description: '', icon: 'users' })

        vi.spyOn(axios, 'put').mockResolvedValueOnce({
            data: { title: 'Updated', description: 'ok' }
        } as any)
        await api.updateGroup('admins', {
            slug: 'admins',
            name: 'Admins',
            description: '',
            icon: 'users'
        })

        vi.spyOn(axios, 'delete').mockResolvedValueOnce({
            data: { title: 'Deleted', description: 'ok' }
        } as any)
        await api.deleteGroup('admins')

        expect(mockPush).toHaveBeenNthCalledWith(1, {
            title: 'Created',
            description: 'ok',
            style: Severity.Success
        })
        expect(mockPush).toHaveBeenNthCalledWith(2, {
            title: 'Updated',
            description: 'ok',
            style: Severity.Success
        })
        expect(mockPush).toHaveBeenNthCalledWith(3, {
            title: 'Deleted',
            description: 'ok',
            style: Severity.Success
        })

        api.formData.value.name = 'Temp'
        api.resetForm()
        expect(api.formData.value).toEqual({
            slug: '',
            name: '',
            description: '',
            icon: 'users'
        })

        vi.spyOn(axios, 'get').mockRejectedValueOnce({
            response: { data: { title: 'Fetch error' } }
        })
        await expect(api.fetchGroup('admins')).rejects.toEqual({ title: 'Fetch error' })

        vi.spyOn(axios, 'post').mockRejectedValueOnce({
            response: { data: { title: 'Create error' } }
        })
        await expect(
            api.createGroup({ slug: 'x', name: 'x', description: '', icon: 'users' })
        ).rejects.toEqual({ title: 'Create error' })

        vi.spyOn(axios, 'put').mockRejectedValueOnce({
            response: { data: { title: 'Update error' } }
        })
        await expect(
            api.updateGroup('admins', {
                slug: 'admins',
                name: 'x',
                description: '',
                icon: 'users'
            })
        ).rejects.toEqual({ title: 'Update error' })

        vi.spyOn(axios, 'delete').mockRejectedValueOnce({
            response: { data: { title: 'Delete error' } }
        })
        await expect(api.deleteGroup('admins')).rejects.toEqual({ title: 'Delete error' })

        expect(api.apiError.value).toEqual({ title: 'Delete error' })
        expect(api.apiLoading.value).toBe(false)
    })

    test('useRoleApi handles slug watch, reset, and all CRUD methods', async () => {
        const api = useRoleApi()

        expect(api.formData.value).toEqual({
            name: '',
            slug: '',
            description: ''
        })

        api.formData.value.name = 'Site Admin'
        await nextTick()
        expect(api.formData.value.slug).toBe('site-admin')
        expect(api.slugLocked.value).toBe(true)

        api.formData.value.slug = 'custom-managers'
        api.formData.value.name = 'Custom Role'
        await nextTick()
        expect(api.formData.value.slug).toBe('custom-managers')
        expect(api.slugLocked.value).toBe(false)

        api.formData.value.name = 'Renamed Custom Role'
        await nextTick()
        expect(api.formData.value.slug).toBe('custom-managers')

        api.slugLocked.value = true
        api.formData.value.slug = 'renamed-custom-role'
        await nextTick()
        expect(api.slugLocked.value).toBe(true)

        api.formData.value.name = 'Role With Generated Slug'
        await nextTick()
        expect(api.formData.value.slug).toBe('role-with-generated-slug')

        api.slugLocked.value = false
        api.formData.value.name = 'Name Change While Unlocked'
        await nextTick()
        expect(api.formData.value.slug).toBe('role-with-generated-slug')

        api.formData.value.slug = 'custom-managers'
        await nextTick()
        api.slugLocked.value = true
        api.formData.value.name = 'Renamed Custom Role Again'
        await nextTick()
        expect(api.formData.value.slug).toBe('custom-managers')
        expect(api.slugLocked.value).toBe(false)

        vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: { slug: 'admin' } } as any)
        await expect(api.fetchRole('admin')).resolves.toEqual({ slug: 'admin' })

        vi.spyOn(axios, 'post').mockResolvedValueOnce({
            data: { title: 'Created', description: 'ok' }
        } as any)
        await api.createRole({ name: 'Admin', slug: 'admin', description: '' })

        vi.spyOn(axios, 'put').mockResolvedValueOnce({
            data: { title: 'Updated', description: 'ok' }
        } as any)
        await api.updateRole('admin', { name: 'Admin', slug: 'admin', description: '' })

        vi.spyOn(axios, 'delete').mockResolvedValueOnce({
            data: { title: 'Deleted', description: 'ok' }
        } as any)
        await api.deleteRole('admin')

        expect(mockPush).toHaveBeenNthCalledWith(1, {
            title: 'Created',
            description: 'ok',
            style: Severity.Success
        })
        expect(mockPush).toHaveBeenNthCalledWith(2, {
            title: 'Updated',
            description: 'ok',
            style: Severity.Success
        })
        expect(mockPush).toHaveBeenNthCalledWith(3, {
            title: 'Deleted',
            description: 'ok',
            style: Severity.Success
        })

        api.formData.value.name = 'Temp'
        api.resetForm()
        expect(api.formData.value).toEqual({
            name: '',
            slug: '',
            description: ''
        })

        vi.spyOn(axios, 'get').mockRejectedValueOnce({
            response: { data: { title: 'Fetch error' } }
        })
        await expect(api.fetchRole('admin')).rejects.toEqual({ title: 'Fetch error' })

        vi.spyOn(axios, 'post').mockRejectedValueOnce({
            response: { data: { title: 'Create error' } }
        })
        await expect(api.createRole({ name: 'x', slug: 'x', description: '' })).rejects.toEqual({
            title: 'Create error'
        })

        vi.spyOn(axios, 'put').mockRejectedValueOnce({
            response: { data: { title: 'Update error' } }
        })
        await expect(
            api.updateRole('admin', { name: 'x', slug: 'admin', description: '' })
        ).rejects.toEqual({
            title: 'Update error'
        })

        vi.spyOn(axios, 'delete').mockRejectedValueOnce({
            response: { data: { title: 'Delete error' } }
        })
        await expect(api.deleteRole('admin')).rejects.toEqual({ title: 'Delete error' })

        expect(api.apiError.value).toEqual({ title: 'Delete error' })
        expect(api.apiLoading.value).toBe(false)
    })

    test('useUserApi handles reset and all CRUD methods', async () => {
        const api = useUserApi()

        expect(api.formData.value).toEqual({
            user_name: '',
            first_name: '',
            last_name: '',
            email: '',
            locale: 'users'
        })

        vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: { user_name: 'alice' } } as any)
        await expect(api.fetchUser('alice')).resolves.toEqual({ user_name: 'alice' })

        vi.spyOn(axios, 'post').mockResolvedValueOnce({
            data: { title: 'Created', description: 'ok' }
        } as any)
        await api.createUser({
            user_name: 'alice',
            first_name: 'Alice',
            last_name: 'Doe',
            email: 'alice@example.com',
            locale: 'en_US'
        })

        vi.spyOn(axios, 'put').mockResolvedValueOnce({
            data: { title: 'Updated', description: 'ok' }
        } as any)
        await api.updateUser('alice', {
            user_name: 'alice',
            first_name: 'Alice',
            last_name: 'Doe',
            email: 'alice@example.com',
            locale: 'en_US'
        })

        vi.spyOn(axios, 'delete').mockResolvedValueOnce({
            data: { title: 'Deleted', description: 'ok' }
        } as any)
        await api.deleteUser('alice')

        expect(mockPush).toHaveBeenNthCalledWith(1, {
            title: 'Created',
            description: 'ok',
            style: Severity.Success
        })
        expect(mockPush).toHaveBeenNthCalledWith(2, {
            title: 'Updated',
            description: 'ok',
            style: Severity.Success
        })
        expect(mockPush).toHaveBeenNthCalledWith(3, {
            title: 'Deleted',
            description: 'ok',
            style: Severity.Success
        })

        api.formData.value.user_name = 'temp'
        api.resetForm()
        expect(api.formData.value).toEqual({
            user_name: '',
            first_name: '',
            last_name: '',
            email: '',
            locale: 'users'
        })

        vi.spyOn(axios, 'get').mockRejectedValueOnce({
            response: { data: { title: 'Fetch error' } }
        })
        await expect(api.fetchUser('alice')).rejects.toEqual({ title: 'Fetch error' })

        vi.spyOn(axios, 'post').mockRejectedValueOnce({
            response: { data: { title: 'Create error' } }
        })
        await expect(
            api.createUser({
                user_name: 'x',
                first_name: 'x',
                last_name: 'x',
                email: 'x@x.com',
                locale: 'en_US'
            })
        ).rejects.toEqual({ title: 'Create error' })

        vi.spyOn(axios, 'put').mockRejectedValueOnce({
            response: { data: { title: 'Update error' } }
        })
        await expect(
            api.updateUser('alice', {
                user_name: 'alice',
                first_name: 'x',
                last_name: 'x',
                email: 'x@x.com',
                locale: 'en_US'
            })
        ).rejects.toEqual({ title: 'Update error' })

        vi.spyOn(axios, 'delete').mockRejectedValueOnce({
            response: { data: { title: 'Delete error' } }
        })
        await expect(api.deleteUser('alice')).rejects.toEqual({ title: 'Delete error' })

        expect(api.apiError.value).toEqual({ title: 'Delete error' })
        expect(api.apiLoading.value).toBe(false)
    })
})
