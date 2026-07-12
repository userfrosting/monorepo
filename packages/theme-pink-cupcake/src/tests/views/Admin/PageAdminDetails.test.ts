import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import PageGroup from '../../../views/Admin/PageGroup.vue'
import PagePermission from '../../../views/Admin/PagePermission.vue'
import PageRole from '../../../views/Admin/PageRole.vue'
import PageUser from '../../../views/Admin/PageUser.vue'

const route = {
    params: {
        slug: 'admins',
        id: '12',
        user_name: 'jane'
    }
}
const pageMeta = { title: '', hideTitle: false, hideBreadcrumbs: false }
const groupError = ref<any>(null)
const roleError = ref<any>(null)
const userError = ref<any>(null)
const permissionError = ref<any>(null)
const permission = ref({ id: 12, name: 'Permission Name' })
const fetchGroup = vi.fn().mockResolvedValue({ name: 'Admins', slug: 'admins' })
const fetchRole = vi.fn().mockResolvedValue({ name: 'Administrators', slug: 'admins' })
const fetchUser = vi.fn().mockResolvedValue({ full_name: 'Jane Doe', user_name: 'jane' })

vi.mock('vue-router', () => ({
    useRoute: () => route
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    usePageMeta: () => pageMeta
}))

vi.mock('@userfrosting/sprinkle-admin/composables', () => ({
    useGroupApi: () => ({ fetchGroup, apiError: groupError }),
    usePermissionApi: () => ({ permission, error: permissionError }),
    useRoleApi: () => ({ fetchRole, apiError: roleError }),
    useUserApi: () => ({ fetchUser, apiError: userError })
}))

describe('Admin detail views', () => {
    beforeEach(() => {
        pageMeta.title = ''
        groupError.value = null
        permissionError.value = null
        roleError.value = null
        userError.value = null
    })

    afterEach(() => {
        vi.clearAllMocks()
    })

    test('PageGroup fetches group, updates page title, and renders child panels', async () => {
        const wrapper = mount(PageGroup, {
            global: {
                mocks: { $checkAccess: () => true, $route: route },
                stubs: {
                    GroupInfo: { template: '<div data-test="group-info" />' },
                    GroupUsers: { template: '<div data-test="group-users" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })

        await vi.waitFor(() => {
            expect(fetchGroup).toHaveBeenCalledWith('admins')
            expect(pageMeta.title).toBe('Admins')
        })

        expect(wrapper.find('[data-test="group-info"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="group-users"]').exists()).toBe(true)
    })

    test('PagePermission updates title from permission and renders error branch', async () => {
        const okWrapper = mount(PagePermission, {
            global: {
                mocks: { $route: route },
                stubs: {
                    PermissionInfo: { template: '<div data-test="permission-info" />' },
                    PermissionUsers: { template: '<div data-test="permission-users" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })

        await vi.waitFor(() => {
            expect(pageMeta.title).toBe('Permission Name')
        })
        expect(okWrapper.find('[data-test="permission-info"]').exists()).toBe(true)

        permissionError.value = { status: 404 }
        const errorWrapper = mount(PagePermission, {
            global: {
                mocks: { $route: route },
                stubs: {
                    PermissionInfo: true,
                    PermissionUsers: true,
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })
        expect(errorWrapper.find('[data-test="error-page"]').exists()).toBe(true)
    })

    test('PageRole fetches role and supports error branch', async () => {
        const wrapper = mount(PageRole, {
            global: {
                mocks: { $checkAccess: () => true },
                stubs: {
                    RoleInfo: { template: '<div data-test="role-info" />' },
                    RoleUsers: { template: '<div data-test="role-users" />' },
                    RolePermissions: { template: '<div data-test="role-permissions" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })

        await vi.waitFor(() => {
            expect(fetchRole).toHaveBeenCalledWith('admins')
            expect(pageMeta.title).toBe('Administrators')
        })
        expect(wrapper.find('[data-test="role-info"]').exists()).toBe(true)

        roleError.value = { status: 500 }
        const errorWrapper = mount(PageRole, {
            global: {
                mocks: { $checkAccess: () => true },
                stubs: {
                    RoleInfo: true,
                    RoleUsers: true,
                    RolePermissions: true,
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })
        expect(errorWrapper.find('[data-test="error-page"]').exists()).toBe(true)
    })

    test('PageUser fetches user and supports error branch', async () => {
        const wrapper = mount(PageUser, {
            global: {
                mocks: { $checkAccess: () => true },
                stubs: {
                    UserInfo: { template: '<div data-test="user-info" />' },
                    UserRoles: { template: '<div data-test="user-roles" />' },
                    UserPermissions: { template: '<div data-test="user-permissions" />' },
                    UserActivities: { template: '<div data-test="user-activities" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })

        await vi.waitFor(() => {
            expect(fetchUser).toHaveBeenCalledWith('jane')
            expect(pageMeta.title).toBe('Jane Doe')
        })
        expect(wrapper.find('[data-test="user-info"]').exists()).toBe(true)

        userError.value = { status: 403 }
        const errorWrapper = mount(PageUser, {
            global: {
                mocks: { $checkAccess: () => true },
                stubs: {
                    UserInfo: true,
                    UserRoles: true,
                    UserPermissions: true,
                    UserActivities: true,
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })
        expect(errorWrapper.find('[data-test="error-page"]').exists()).toBe(true)
    })

    test('applies access gating for role, group and user side panels', async () => {
        const groupNoUsers = mount(PageGroup, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) => permission !== 'view_group_field',
                    $route: route
                },
                stubs: {
                    GroupInfo: { template: '<div data-test="group-info" />' },
                    GroupUsers: { template: '<div data-test="group-users" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })
        await vi.waitFor(() => expect(fetchGroup).toHaveBeenCalled())
        expect(groupNoUsers.find('[data-test="group-users"]').exists()).toBe(false)

        const rolePartial = mount(PageRole, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) => permission === 'view_role_field'
                },
                stubs: {
                    RoleInfo: { template: '<div data-test="role-info" />' },
                    RoleUsers: { template: '<div data-test="role-users" />' },
                    RolePermissions: { template: '<div data-test="role-permissions" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })
        await vi.waitFor(() => expect(fetchRole).toHaveBeenCalled())
        expect(rolePartial.find('[data-test="role-users"]').exists()).toBe(false)
        expect(rolePartial.find('[data-test="role-permissions"]').exists()).toBe(true)

        const userPartial = mount(PageUser, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) => permission === 'view_user_permissions'
                },
                stubs: {
                    UserInfo: { template: '<div data-test="user-info" />' },
                    UserRoles: { template: '<div data-test="user-roles" />' },
                    UserPermissions: { template: '<div data-test="user-permissions" />' },
                    UserActivities: { template: '<div data-test="user-activities" />' },
                    UFErrorPage: { template: '<div data-test="error-page" />' }
                }
            }
        })
        await vi.waitFor(() => expect(fetchUser).toHaveBeenCalled())
        expect(userPartial.find('[data-test="user-roles"]').exists()).toBe(false)
        expect(userPartial.find('[data-test="user-permissions"]').exists()).toBe(true)
        expect(userPartial.find('[data-test="user-activities"]').exists()).toBe(false)
    })
})
