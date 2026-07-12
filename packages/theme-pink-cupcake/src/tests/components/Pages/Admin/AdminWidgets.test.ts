import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import DashboardActivities from '../../../../components/Pages/Admin/Dashboard/DashboardActivities.vue'
import DashboardRecentUsers from '../../../../components/Pages/Admin/Dashboard/DashboardRecentUsers.vue'
import GroupInfo from '../../../../components/Pages/Admin/Group/GroupInfo.vue'
import GroupUsers from '../../../../components/Pages/Admin/Group/GroupUsers.vue'
import PermissionInfo from '../../../../components/Pages/Admin/Permission/PermissionInfo.vue'
import PermissionUsers from '../../../../components/Pages/Admin/Permission/PermissionUsers.vue'
import RoleInfo from '../../../../components/Pages/Admin/Role/RoleInfo.vue'
import RolePermissions from '../../../../components/Pages/Admin/Role/RolePermissions.vue'
import RoleUsers from '../../../../components/Pages/Admin/Role/RoleUsers.vue'
import UserInfo from '../../../../components/Pages/Admin/User/UserInfo.vue'
import UserActivities from '../../../../components/Pages/Admin/User/UserActivities.vue'
import UserPermissions from '../../../../components/Pages/Admin/User/UserPermissions.vue'
import UserRoles from '../../../../components/Pages/Admin/User/UserRoles.vue'
import type { UserInterface, GroupInterface } from '@userfrosting/sprinkle-account/interfaces'
import type { UserResponse, GroupResponse } from '@userfrosting/sprinkle-admin/interfaces'

const push = vi.fn()

const baseGroup: GroupInterface = {
    id: 1,
    slug: 'admins',
    name: 'Admins',
    description: 'Desc',
    icon: 'users',
    created_at: '',
    updated_at: '',
    deleted_at: null
}

const adminGroupResponse: GroupResponse = {
    ...baseGroup,
    users_count: 3
}

const baseUser: UserInterface = {
    id: 1,
    user_name: 'jane',
    first_name: 'Jane',
    last_name: 'Doe',
    full_name: 'Jane Doe',
    email: 'jane@example.com',
    avatar: '/avatar.png',
    flag_enabled: true,
    flag_verified: false,
    group_id: 1,
    locale: 'en_US',
    created_at: '2024-01-01',
    updated_at: '2024-01-01',
    deleted_at: null
}

const adminUserResponse: UserResponse = {
    ...baseUser,
    locale_name: 'English',
    group: baseGroup
}

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        getDateTime: () => ({
            toRelative: () => 'relative time'
        })
    })
}))

vi.mock('vue-router', () => ({
    useRouter: () => ({ push })
}))

const SprunjeTableStub = {
    template: `
        <div data-test="sprunje-table">
            <slot name="actions" :sprunjer="sprunjer" />
            <slot name="header" />
            <slot name="body" :row="row" :sprunjer="sprunjer" />
        </div>
    `,
    data() {
        return {
            sprunjer: { fetch: vi.fn() },
            row: {
                id: 1,
                slug: 'admins',
                name: 'Admins',
                description: 'Admin role',
                user_name: 'jane',
                full_name: 'Jane Doe',
                email: 'jane@example.com',
                users_count: 3,
                occurred_at: '2024-01-01',
                ip_address: '127.0.0.1',
                flag_enabled: true,
                flag_verified: false,
                user: {
                    user_name: 'jane',
                    full_name: 'Jane Doe',
                    email: 'jane@example.com'
                },
                roles_via: [{ id: 10, slug: 'admins', name: 'Admins' }]
            }
        }
    }
}

const global = {
    mocks: {
        $checkAccess: () => true,
        $t: (key: string) => key,
        $tdate: (value: string) => value
    },
    stubs: {
        UFCardBox: { template: '<div><slot /><slot name="footer" /></div>' },
        UFSprunjeTable: SprunjeTableStub,
        UFSprunjeHeader: { template: '<div><slot /></div>' },
        UFSprunjeColumn: { template: '<div><slot /></div>' },
        RouterLink: { template: '<a><slot /></a>' },
        UFLabel: { template: '<span><slot /></span>' },
        'font-awesome-icon': { template: '<span />' },
        GroupEditModal: {
            emits: ['saved'],
            template: '<button data-test="group-edit" @click="$emit(\'saved\')" />'
        },
        GroupDeleteModal: {
            emits: ['deleted'],
            template: '<button data-test="group-delete" @click="$emit(\'deleted\')" />'
        },
        RoleEditModal: {
            emits: ['saved'],
            template: '<button data-test="role-edit" @click="$emit(\'saved\')" />'
        },
        RoleDeleteModal: {
            emits: ['deleted'],
            template: '<button data-test="role-delete" @click="$emit(\'deleted\')" />'
        },
        RoleManagePermissionModal: {
            emits: ['saved'],
            template: '<button data-test="role-manage" @click="$emit(\'saved\')" />'
        },
        UserEditModal: {
            emits: ['saved'],
            template: '<button data-test="user-edit" @click="$emit(\'saved\')" />'
        },
        UserDeleteModal: {
            emits: ['deleted'],
            template: '<button data-test="user-delete" @click="$emit(\'deleted\')" />'
        },
        UserActivateModal: {
            emits: ['saved'],
            template: '<button data-test="user-activate" @click="$emit(\'saved\')" />'
        },
        UserPasswordModal: { template: '<div data-test="user-password" />' },
        UserPasswordResetModal: { template: '<div data-test="user-password-reset" />' },
        UserManageRolesModal: {
            emits: ['saved'],
            template: '<button data-test="user-manage-roles" @click="$emit(\'saved\')" />'
        }
    }
}

describe('admin widget components', () => {
    beforeEach(() => {
        push.mockClear()
    })

    test('renders dashboard widgets', () => {
        const activities = mount(DashboardActivities, { global })
        const recentUsers = mount(DashboardRecentUsers, {
            props: {
                users: [baseUser]
            },
            global: {
                ...global,
                mocks: {
                    ...global.mocks,
                    $t: (key: string) => key
                },
                stubs: global.stubs
            }
        })

        expect(activities.find('[data-test="sprunje-table"]').exists()).toBe(true)
        expect(recentUsers.text()).toContain('Jane Doe')
        expect(recentUsers.text()).toContain('USER.VIEW_ALL')
    })

    test('renders group and permission widgets', async () => {
        const groupInfo = mount(GroupInfo, {
            props: {
                group: adminGroupResponse
            },
            global
        })
        await groupInfo.get('[data-test="group-edit"]').trigger('click')
        await groupInfo.get('[data-test="group-delete"]').trigger('click')
        expect(groupInfo.emitted('groupUpdated')).toHaveLength(1)
        expect(push).toHaveBeenCalledWith({ name: 'admin.groups' })

        const groupUsers = mount(GroupUsers, { props: { slug: 'admins' }, global })
        expect(groupUsers.find('[data-test="sprunje-table"]').exists()).toBe(true)
        expect(groupUsers.text()).toContain('ENABLED')

        const permissionInfo = mount(PermissionInfo, {
            props: {
                permission: {
                    id: 1,
                    slug: 'perm.slug',
                    name: 'Permission',
                    conditions: 'always()',
                    description: 'Desc',
                    created_at: '',
                    updated_at: '',
                    deleted_at: null
                }
            },
            global
        })
        expect(permissionInfo.text()).toContain('perm.slug')
        expect(permissionInfo.text()).toContain('always()')

        const permissionUsers = mount(PermissionUsers, { props: { id: 1 }, global })
        expect(permissionUsers.find('[data-test="sprunje-table"]').exists()).toBe(true)
        expect(permissionUsers.text()).toContain('Admins')
    })

    test('renders role and user widgets', async () => {
        const roleInfo = mount(RoleInfo, {
            props: {
                role: {
                    id: 1,
                    name: 'Admins',
                    slug: 'admins',
                    description: 'Desc',
                    created_at: '',
                    updated_at: '',
                    deleted_at: null,
                    users_count: 2
                }
            },
            global
        })
        await roleInfo.get('[data-test="role-edit"]').trigger('click')
        await roleInfo.get('[data-test="role-delete"]').trigger('click')
        expect(roleInfo.emitted('updated')).toHaveLength(1)
        expect(push).toHaveBeenCalledWith({ name: 'admin.roles' })

        const roleUsers = mount(RoleUsers, { props: { slug: 'admins' }, global })
        const rolePermissions = mount(RolePermissions, {
            props: {
                role: {
                    id: 1,
                    slug: 'admins',
                    name: 'Admins',
                    description: '',
                    created_at: '',
                    updated_at: '',
                    deleted_at: null,
                    users_count: 2
                }
            },
            global
        })
        expect(roleUsers.find('[data-test="sprunje-table"]').exists()).toBe(true)
        expect(rolePermissions.find('[data-test="sprunje-table"]').exists()).toBe(true)

        const userInfo = mount(UserInfo, {
            props: {
                user: adminUserResponse
            },
            global
        })
        await userInfo.get('[data-test="user-edit"]').trigger('click')
        await userInfo.get('[data-test="user-activate"]').trigger('click')
        await userInfo.get('[data-test="user-delete"]').trigger('click')
        expect(userInfo.emitted('updated')).toHaveLength(2)
        expect(push).toHaveBeenCalledWith({ name: 'admin.users' })
        expect(userInfo.text()).toContain('UNVERIFIED')

        const userActivities = mount(UserActivities, { props: { user_name: 'jane' }, global })
        const userPermissions = mount(UserPermissions, { props: { user_name: 'jane' }, global })
        const userRoles = mount(UserRoles, {
            props: {
                user: {
                    ...baseUser,
                    flag_verified: true
                }
            },
            global
        })
        expect(userActivities.find('[data-test="sprunje-table"]').exists()).toBe(true)
        expect(userPermissions.find('[data-test="sprunje-table"]').exists()).toBe(true)
        expect(userRoles.find('[data-test="sprunje-table"]').exists()).toBe(true)
    })

    test('renders user info fallback branches and access gating', () => {
        const restrictedGlobal = {
            ...global,
            mocks: {
                ...global.mocks,
                $checkAccess: (permission: string) => permission !== 'view_user_field'
            }
        }

        const restricted = mount(UserInfo, {
            props: {
                user: {
                    ...adminUserResponse,
                    group: null,
                    flag_verified: true,
                    flag_enabled: false,
                    user_name: ''
                }
            },
            global: restrictedGlobal
        })

        expect(restricted.find('[data-test="meta"]').exists()).toBe(false)
        expect(restricted.text()).not.toContain('UNVERIFIED')
        expect(restricted.text()).not.toContain('ACTIVE')

        const fullAccess = mount(UserInfo, {
            props: {
                user: {
                    ...adminUserResponse,
                    group: null,
                    flag_verified: true,
                    flag_enabled: false,
                    user_name: ''
                }
            },
            global
        })

        expect(fullAccess.text()).toContain('VERIFIED')
        expect(fullAccess.text()).toContain('DISABLED')
        expect(fullAccess.text()).toContain('NONE')
    })
})
