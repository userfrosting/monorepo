import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import {
    ActivitiesView,
    DashboardView,
    GroupView,
    GroupsView,
    PermissionView,
    PermissionsView,
    RoleView,
    RolesView,
    UserView,
    UsersView
} from '../../views'
import PageConfig from '../../views/PageConfig.vue'
import PageConfigCache from '../../views/PageConfigCache.vue'
import PageConfigInfo from '../../views/PageConfigInfo.vue'

const marker = (id: string) => ({
    template: `<div data-test="${id}" />`
})

describe('views wrappers', () => {
    test('exports expected views from views/index.ts', () => {
        expect(ActivitiesView).toBeDefined()
        expect(DashboardView).toBeDefined()
        expect(GroupView).toBeDefined()
        expect(GroupsView).toBeDefined()
        expect(PermissionView).toBeDefined()
        expect(PermissionsView).toBeDefined()
        expect(RoleView).toBeDefined()
        expect(RolesView).toBeDefined()
        expect(UserView).toBeDefined()
        expect(UsersView).toBeDefined()
    })

    test('renders wrapper pages', () => {
        const pages = [
            mount(ActivitiesView, {
                global: { stubs: { UFAdminActivitiesPage: marker('activities') } }
            }),
            mount(DashboardView, {
                global: { stubs: { UFAdminDashboardPage: marker('dashboard') } }
            }),
            mount(GroupView, {
                global: { stubs: { UFAdminGroupPage: marker('group') } }
            }),
            mount(GroupsView, {
                global: { stubs: { UFAdminGroupsPage: marker('groups') } }
            }),
            mount(PermissionView, {
                global: { stubs: { UFAdminPermissionPage: marker('permission') } }
            }),
            mount(PermissionsView, {
                global: { stubs: { UFAdminPermissionsPage: marker('permissions') } }
            }),
            mount(RoleView, {
                global: { stubs: { UFAdminRolePage: marker('role') } }
            }),
            mount(RolesView, {
                global: { stubs: { UFAdminRolesPage: marker('roles') } }
            }),
            mount(UserView, {
                global: { stubs: { UFAdminUserPage: marker('user') } }
            }),
            mount(UsersView, {
                global: { stubs: { UFAdminUsersPage: marker('users') } }
            }),
            mount(PageConfig, {
                global: { stubs: { UFAdminConfigPage: marker('config') } }
            }),
            mount(PageConfigInfo, {
                global: { stubs: { UFAdminConfigInfoPage: marker('config-info') } }
            }),
            mount(PageConfigCache, {
                global: { stubs: { UFAdminConfigCachePage: marker('config-cache') } }
            })
        ]

        const selectors = [
            'activities',
            'dashboard',
            'group',
            'groups',
            'permission',
            'permissions',
            'role',
            'roles',
            'user',
            'users',
            'config',
            'config-info',
            'config-cache'
        ]

        for (const [index, wrapper] of pages.entries()) {
            expect(wrapper.find(`[data-test="${selectors[index]}"]`).exists()).toBe(true)
        }
    })
})
