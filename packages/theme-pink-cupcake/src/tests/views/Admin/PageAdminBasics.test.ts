import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import PageActivities from '../../../views/Admin/PageActivities.vue'
import PageConfig from '../../../views/Admin/PageConfig.vue'
import PageConfigCache from '../../../views/Admin/PageConfigCache.vue'
import PageConfigInfo from '../../../views/Admin/PageConfigInfo.vue'
import PageDashboard from '../../../views/Admin/PageDashboard.vue'
import PageGroups from '../../../views/Admin/PageGroups.vue'
import PagePermissions from '../../../views/Admin/PagePermissions.vue'
import PageRoles from '../../../views/Admin/PageRoles.vue'
import PageUsers from '../../../views/Admin/PageUsers.vue'

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
            sprunjer: { fetch: () => {} },
            row: {
                slug: 's',
                id: 1,
                name: 'Name',
                description: 'Desc',
                users_count: 1,
                user_name: 'jane',
                full_name: 'Jane',
                email: 'jane@example.com',
                flag_enabled: true,
                flag_verified: true
            }
        }
    }
}

const mockDashboardLoad = vi.fn()
const mockClearCache = vi.fn()
const dashboardData = ref({
    counter: { users: 3, roles: 2, groups: 1 },
    users: [{ id: 1, full_name: 'Jane Doe' }]
})
const configInfoLoad = vi.fn()
const configInfoData = ref({
    frameworkVersion: '6.0',
    phpVersion: '8.4',
    database: { connection: 'mysql', name: 'db', type: 'mysql', version: '8.0' },
    server: 'localhost',
    projectPath: '/app',
    sprinkles: ['core', 'account']
})

vi.mock('@userfrosting/sprinkle-admin/composables', () => ({
    useDashboardApi: () => ({
        load: mockDashboardLoad,
        data: dashboardData.value
    }),
    useConfigCacheApi: () => ({
        clearCache: mockClearCache,
        loading: ref(false)
    }),
    useConfigSystemInfoApi: () => ({
        load: configInfoLoad,
        data: configInfoData.value
    })
}))

describe('Admin basic views', () => {
    test('renders dashboard cards and calls load', () => {
        const wrapper = mount(PageDashboard, {
            global: {
                mocks: {
                    $checkAccess: () => true,
                    $t: (key: string) => key
                },
                stubs: {
                    UFInfoBox: {
                        props: ['value', 'label', 'to'],
                        template: '<div class="info-box" :data-route="to.name" :data-value="value" />'
                    },
                    DashboardRecentUsers: { template: '<div data-test="recent-users" />' },
                    DashboardActivities: { template: '<div data-test="activities" />' }
                }
            }
        })

        expect(mockDashboardLoad).toHaveBeenCalled()
        expect(wrapper.findAll('.info-box')).toHaveLength(3)
        expect(wrapper.find('[data-test="recent-users"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="activities"]').exists()).toBe(true)
    })

    test('renders config navigation based on access', () => {
        const wrapper = mount(PageConfig, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) => permission === 'view_system_info',
                    $t: (key: string) => key
                },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' },
                    UFSideBarItem: {
                        props: ['to'],
                        template: '<div class="sidebar-item" :data-route="to.name" />'
                    },
                    RouterView: { template: '<div data-test="router-view" />' }
                }
            }
        })

        const items = wrapper.findAll('.sidebar-item')
        expect(items).toHaveLength(1)
        expect(items[0].attributes('data-route')).toBe('admin.config.info')
    })

    test('renders config info and triggers load', () => {
        const wrapper = mount(PageConfigInfo, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' }
                }
            }
        })

        expect(configInfoLoad).toHaveBeenCalled()
        expect(wrapper.text()).toContain('6.0')
        expect(wrapper.text()).toContain('8.4')
        expect(wrapper.text()).toContain('localhost')
        expect(wrapper.text()).toContain('/app')
        expect(wrapper.text()).toContain('core')
    })

    test('renders config cache button and confirms clear cache', async () => {
        const wrapper = mount(PageConfigCache, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' },
                    UFModalConfirmation: {
                        emits: ['confirmed'],
                        template: '<button data-test="confirm" @click="$emit(\'confirmed\')" />'
                    }
                }
            }
        })

        await wrapper.get('[data-test="confirm"]').trigger('click')
        expect(mockClearCache).toHaveBeenCalled()
    })

    test('renders list pages with their table/actions structure', () => {
        const stubs = {
            UFCardBox: { template: '<div><slot /></div>' },
            UFSprunjeTable: SprunjeTableStub,
            UFSprunjeHeader: { template: '<div><slot /></div>' },
            UFSprunjeColumn: { template: '<div><slot /></div>' },
            RouterLink: { template: '<a><slot /></a>' },
            GroupCreateModal: { template: '<div data-test="group-create" />' },
            GroupEditModal: { template: '<div data-test="group-edit" />' },
            GroupDeleteModal: { template: '<div data-test="group-delete" />' },
            RoleCreateModal: { template: '<div data-test="role-create" />' },
            RoleEditModal: { template: '<div data-test="role-edit" />' },
            RoleDeleteModal: { template: '<div data-test="role-delete" />' },
            RoleManagePermissionModal: { template: '<div data-test="role-manage" />' },
            UserCreateModal: { template: '<div data-test="user-create" />' },
            UserEditModal: { template: '<div data-test="user-edit" />' },
            UserDeleteModal: { template: '<div data-test="user-delete" />' },
            UserActivateModal: { template: '<div data-test="user-activate" />' },
            UserPasswordModal: { template: '<div data-test="user-password" />' },
            UserPasswordResetModal: { template: '<div data-test="user-password-reset" />' },
            UFLabel: { template: '<div><slot /></div>' },
            'font-awesome-icon': { template: '<span />' }
        }
        const global = {
            mocks: {
                $checkAccess: () => true,
                $t: (key: string) => key,
                $tdate: (value: string) => value
            },
            stubs
        }

        for (const component of [
            PageActivities,
            PageGroups,
            PagePermissions,
            PageRoles,
            PageUsers
        ]) {
            const wrapper = mount(component, { global })
            expect(wrapper.find('[data-test="sprunje-table"]').exists()).toBe(true)
        }
    })

    test('renders role and user list conditional content branches', () => {
        const richRowTableStub = {
            template: `
                <div data-test="sprunje-table">
                    <slot name="actions" :sprunjer="sprunjer" />
                    <slot name="header" />
                    <slot name="body" :row="row" :sprunjer="sprunjer" />
                    <slot name="body" :row="emptyRow" :sprunjer="sprunjer" />
                </div>
            `,
            data() {
                return {
                    sprunjer: { fetch: () => {} },
                    row: {
                        id: 1,
                        slug: 'admins',
                        name: 'Admins',
                        description: 'Desc',
                        user_name: 'jane',
                        full_name: 'Jane Doe',
                        email: 'jane@example.com',
                        last_activity: {
                            description: 'Logged in',
                            occurred_at: '2024-01-01'
                        },
                        flag_enabled: false,
                        flag_verified: false
                    },
                    emptyRow: {
                        id: 2,
                        slug: 'users',
                        name: 'Users',
                        description: 'Other',
                        user_name: 'john',
                        full_name: 'John Doe',
                        email: 'john@example.com',
                        last_activity: null,
                        flag_enabled: true,
                        flag_verified: true
                    }
                }
            }
        }

        const baseStubs = {
            UFCardBox: { template: '<div><slot /></div>' },
            UFSprunjeTable: richRowTableStub,
            UFSprunjeHeader: { template: '<div><slot /></div>' },
            UFSprunjeColumn: { template: '<div><slot /></div>' },
            RouterLink: { template: '<a><slot /></a>' },
            RoleCreateModal: { template: '<div data-test="role-create" />' },
            RoleEditModal: { template: '<div data-test="role-edit" />' },
            RoleDeleteModal: { template: '<div data-test="role-delete" />' },
            RoleManagePermissionModal: { template: '<div data-test="role-manage" />' },
            UserCreateModal: { template: '<div data-test="user-create" />' },
            UserEditModal: { template: '<div data-test="user-edit" />' },
            UserDeleteModal: { template: '<div data-test="user-delete" />' },
            UserActivateModal: { template: '<div data-test="user-activate" />' },
            UserPasswordModal: { template: '<div data-test="user-password" />' },
            UserPasswordResetModal: { template: '<div data-test="user-password-reset" />' },
            UFLabel: { template: '<div><slot /></div>' },
            'font-awesome-icon': { template: '<span />' }
        }

        const withAccess = mount(PageUsers, {
            global: {
                mocks: {
                    $checkAccess: () => true,
                    $t: (key: string) => key,
                    $tdate: (value: string) => value
                },
                stubs: baseStubs
            }
        })

        expect(withAccess.text()).toContain('ACTIVITY.NONE')
        expect(withAccess.text()).toContain('DISABLED')
        expect(withAccess.text()).toContain('UNVERIFIED')
        expect(withAccess.find('[data-test="user-create"]').exists()).toBe(true)

        const noCreateAccess = mount(PageRoles, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) => permission !== 'create_role',
                    $t: (key: string) => key,
                    $tdate: (value: string) => value
                },
                stubs: baseStubs
            }
        })

        expect(noCreateAccess.find('[data-test="role-create"]').exists()).toBe(false)
        expect(noCreateAccess.find('[data-test="role-manage"]').exists()).toBe(true)
    })

    test('renders activities deleted-user fallback and groups access branches', () => {
        const activityAndGroupTableStub = {
            template: `
                <div data-test="sprunje-table">
                    <slot name="actions" :sprunjer="sprunjer" />
                    <slot name="header" />
                    <slot name="body" :row="withUser" :sprunjer="sprunjer" />
                    <slot name="body" :row="withoutUser" :sprunjer="sprunjer" />
                </div>
            `,
            data() {
                return {
                    sprunjer: { fetch: () => {} },
                    withUser: {
                        id: 1,
                        slug: 'admins',
                        name: 'Admins',
                        description: 'Desc',
                        users_count: 2,
                        occurred_at: '2024-01-01',
                        ip_address: '127.0.0.1',
                        user: {
                            user_name: 'jane',
                            full_name: 'Jane Doe',
                            email: 'jane@example.com'
                        }
                    },
                    withoutUser: {
                        id: 2,
                        slug: 'users',
                        name: 'Users',
                        description: 'Users group',
                        users_count: 4,
                        occurred_at: '2024-01-02',
                        ip_address: '127.0.0.2',
                        user: null
                    }
                }
            }
        }

        const stubs = {
            UFCardBox: { template: '<div><slot /></div>' },
            UFSprunjeTable: activityAndGroupTableStub,
            UFSprunjeHeader: { template: '<div><slot /></div>' },
            UFSprunjeColumn: { template: '<div><slot /></div>' },
            RouterLink: { template: '<a><slot /></a>' },
            GroupCreateModal: { template: '<div data-test="group-create" />' },
            GroupEditModal: { template: '<div data-test="group-edit" />' },
            GroupDeleteModal: { template: '<div data-test="group-delete" />' },
            'font-awesome-icon': { template: '<span />' }
        }

        const activities = mount(PageActivities, {
            global: {
                mocks: {
                    $checkAccess: () => true,
                    $t: (key: string) => key,
                    $tdate: (value: string) => value
                },
                stubs
            }
        })

        expect(activities.text()).toContain('USER.DELETED')

        const groupsNoAccess = mount(PageGroups, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) => permission === 'uri_group',
                    $t: (key: string) => key,
                    $tdate: (value: string) => value
                },
                stubs
            }
        })

        expect(groupsNoAccess.find('[data-test="group-create"]').exists()).toBe(false)
        expect(groupsNoAccess.find('[data-test="group-edit"]').exists()).toBe(false)
        expect(groupsNoAccess.find('[data-test="group-delete"]').exists()).toBe(false)
        expect(groupsNoAccess.text()).toContain('Admins')
    })

    test('invokes sprunjer.fetch from list page modal action handlers', async () => {
        const fetchSpy = vi.fn()
        const actionTableStub = {
            template: `
                <div data-test="sprunje-table">
                    <slot name="actions" :sprunjer="sprunjer" />
                    <slot name="body" :row="row" :sprunjer="sprunjer" />
                </div>
            `,
            data() {
                return {
                    sprunjer: { fetch: fetchSpy },
                    row: {
                        id: 1,
                        slug: 'admins',
                        user_name: 'jane',
                        full_name: 'Jane Doe',
                        email: 'jane@example.com',
                        description: 'Desc',
                        users_count: 1,
                        flag_enabled: true,
                        flag_verified: true
                    }
                }
            }
        }

        const commonStubs = {
            UFCardBox: { template: '<div><slot /></div>' },
            UFSprunjeTable: actionTableStub,
            UFSprunjeHeader: { template: '<div><slot /></div>' },
            UFSprunjeColumn: { template: '<div><slot /></div>' },
            RouterLink: { template: '<a><slot /></a>' },
            UFLabel: { template: '<div><slot /></div>' },
            'font-awesome-icon': { template: '<span />' }
        }

        const groups = mount(PageGroups, {
            global: {
                mocks: { $checkAccess: () => true, $t: (key: string) => key, $tdate: (v: string) => v },
                stubs: {
                    ...commonStubs,
                    GroupCreateModal: { emits: ['saved'], template: '<button data-test="group-create" @click="$emit(\'saved\')" />' },
                    GroupEditModal: { emits: ['saved'], template: '<button data-test="group-edit" @click="$emit(\'saved\')" />' },
                    GroupDeleteModal: { emits: ['deleted'], template: '<button data-test="group-delete" @click="$emit(\'deleted\')" />' }
                }
            }
        })
        await groups.get('[data-test="group-create"]').trigger('click')
        await groups.get('[data-test="group-edit"]').trigger('click')
        await groups.get('[data-test="group-delete"]').trigger('click')

        const roles = mount(PageRoles, {
            global: {
                mocks: { $checkAccess: () => true, $t: (key: string) => key, $tdate: (v: string) => v },
                stubs: {
                    ...commonStubs,
                    RoleCreateModal: { emits: ['saved'], template: '<button data-test="role-create" @click="$emit(\'saved\')" />' },
                    RoleEditModal: { emits: ['saved'], template: '<button data-test="role-edit" @click="$emit(\'saved\')" />' },
                    RoleDeleteModal: { emits: ['deleted'], template: '<button data-test="role-delete" @click="$emit(\'deleted\')" />' },
                    RoleManagePermissionModal: { template: '<div />' }
                }
            }
        })
        await roles.get('[data-test="role-create"]').trigger('click')
        await roles.get('[data-test="role-edit"]').trigger('click')
        await roles.get('[data-test="role-delete"]').trigger('click')

        const users = mount(PageUsers, {
            global: {
                mocks: { $checkAccess: () => true, $t: (key: string) => key, $tdate: (v: string) => v },
                stubs: {
                    ...commonStubs,
                    UserCreateModal: { emits: ['saved'], template: '<button data-test="user-create" @click="$emit(\'saved\')" />' },
                    UserEditModal: { emits: ['saved'], template: '<button data-test="user-edit" @click="$emit(\'saved\')" />' },
                    UserDeleteModal: { emits: ['deleted'], template: '<button data-test="user-delete" @click="$emit(\'deleted\')" />' },
                    UserActivateModal: { emits: ['saved'], template: '<button data-test="user-activate" @click="$emit(\'saved\')" />' },
                    UserPasswordModal: { template: '<div />' },
                    UserPasswordResetModal: { template: '<div />' }
                }
            }
        })
        await users.get('[data-test="user-create"]').trigger('click')
        await users.get('[data-test="user-edit"]').trigger('click')
        await users.get('[data-test="user-delete"]').trigger('click')
        await users.get('[data-test="user-activate"]').trigger('click')

        expect(fetchSpy).toHaveBeenCalled()
    })
})
