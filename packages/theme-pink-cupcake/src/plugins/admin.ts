/**
 * Register every admin components & views globally
 * See : https://vuejs.org/guide/components/registration
 */
import type { App } from 'vue'
import {
    UFAdminDashboardPage,
    UFAdminActivitiesPage,
    UFAdminGroupsPage,
    UFAdminGroupPage,
    UFAdminPermissionsPage,
    UFAdminPermissionPage,
    UFAdminRolePage,
    UFAdminRolesPage,
    UFAdminUserPage,
    UFAdminUsersPage,
    UFAdminConfigPage,
    UFAdminConfigInfoPage,
    UFAdminConfigCachePage
} from '../views/Admin'
import {
    DashboardActivities,
    DashboardRecentUsers,
    GroupCreateModal,
    GroupDeleteModal,
    GroupEditModal,
    GroupForm,
    GroupInfo,
    GroupUsers,
    PermissionInfo,
    PermissionUsers,
    RoleInfo,
    RolePermissions,
    RoleUsers,
    UserActivities,
    UserInfo,
    UserPermissions,
    UserRoles
} from '../components/Pages/Admin'

export default {
    install: (app: App) => {
        app.component('UFAdminDashboardPage', UFAdminDashboardPage)
            .component('UFAdminActivitiesPage', UFAdminActivitiesPage)
            .component('UFAdminGroupsPage', UFAdminGroupsPage)
            .component('UFAdminGroupPage', UFAdminGroupPage)
            .component('UFAdminPermissionsPage', UFAdminPermissionsPage)
            .component('UFAdminPermissionPage', UFAdminPermissionPage)
            .component('UFAdminRolePage', UFAdminRolePage)
            .component('UFAdminRolesPage', UFAdminRolesPage)
            .component('UFAdminUserPage', UFAdminUserPage)
            .component('UFAdminUsersPage', UFAdminUsersPage)
            .component('UFAdminConfigPage', UFAdminConfigPage)
            .component('UFAdminConfigInfoPage', UFAdminConfigInfoPage)
            .component('UFAdminConfigCachePage', UFAdminConfigCachePage)
            .component('UFAdminDashboardActivities', DashboardActivities)
            .component('UFAdminDashboardRecentUsers', DashboardRecentUsers)
            .component('UFAdminGroupCreateModal', GroupCreateModal)
            .component('UFAdminGroupDeleteModal', GroupDeleteModal)
            .component('UFAdminGroupEditModal', GroupEditModal)
            .component('UFAdminGroupForm', GroupForm)
            .component('UFAdminGroupInfo', GroupInfo)
            .component('UFAdminGroupUsers', GroupUsers)
            .component('UFAdminPermissionInfo', PermissionInfo)
            .component('UFAdminPermissionUsers', PermissionUsers)
            .component('UFAdminRoleInfo', RoleInfo)
            .component('UFAdminRolePermissions', RolePermissions)
            .component('UFAdminRoleUsers', RoleUsers)
            .component('UFAdminUserActivities', UserActivities)
            .component('UFAdminUserInfo', UserInfo)
            .component('UFAdminUserPermissions', UserPermissions)
            .component('UFAdminUserRoles', UserRoles)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        UFAdminDashboardPage: typeof UFAdminDashboardPage
        UFAdminActivitiesPage: typeof UFAdminActivitiesPage
        UFAdminGroupsPage: typeof UFAdminGroupsPage
        UFAdminGroupPage: typeof UFAdminGroupPage
        UFAdminPermissionsPage: typeof UFAdminPermissionsPage
        UFAdminPermissionPage: typeof UFAdminPermissionPage
        UFAdminRolePage: typeof UFAdminRolePage
        UFAdminRolesPage: typeof UFAdminRolesPage
        UFAdminUserPage: typeof UFAdminUserPage
        UFAdminUsersPage: typeof UFAdminUsersPage
        UFAdminConfigPage: typeof UFAdminConfigPage
        UFAdminConfigInfoPage: typeof UFAdminConfigInfoPage
        UFAdminConfigCachePage: typeof UFAdminConfigCachePage
        UFAdminDashboardActivities: typeof DashboardActivities
        UFAdminDashboardRecentUsers: typeof DashboardRecentUsers
        UFAdminGroupCreateModal: typeof GroupCreateModal
        UFAdminGroupDeleteModal: typeof GroupDeleteModal
        UFAdminGroupEditModal: typeof GroupEditModal
        UFAdminGroupForm: typeof GroupForm
        UFAdminGroupInfo: typeof GroupInfo
        UFAdminGroupUsers: typeof GroupUsers
        UFAdminPermissionInfo: typeof PermissionInfo
        UFAdminPermissionUsers: typeof PermissionUsers
        UFAdminRoleInfo: typeof RoleInfo
        UFAdminRolePermissions: typeof RolePermissions
        UFAdminRoleUsers: typeof RoleUsers
        UFAdminUserActivities: typeof UserActivities
        UFAdminUserInfo: typeof UserInfo
        UFAdminUserPermissions: typeof UserPermissions
        UFAdminUserRoles: typeof UserRoles
    }
}
