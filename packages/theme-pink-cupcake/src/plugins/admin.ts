import type { App } from 'vue'
import {
    UFAdminActivitiesPage,
    UFAdminConfigCachePage,
    UFAdminConfigInfoPage,
    UFAdminConfigPage,
    UFAdminDashboardPage,
    UFAdminGroupPage,
    UFAdminGroupsPage,
    UFAdminPermissionPage,
    UFAdminPermissionsPage,
    UFAdminRolePage,
    UFAdminRolesPage,
    UFAdminUserPage,
    UFAdminUsersPage
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
    RoleCreateModal,
    RoleDeleteModal,
    RoleEditModal,
    RoleForm,
    RoleInfo,
    RoleManagePermissionModal,
    RolePermissions,
    RoleUsers,
    UserActivateModal,
    UserActivities,
    UserCreateModal,
    UserDeleteModal,
    UserEditModal,
    UserForm,
    UserInfo,
    UserManageRolesModal,
    UserPasswordForm,
    UserPasswordModal,
    UserPasswordResetModal,
    UserPermissions,
    UserRoles
} from '../components/Pages/Admin'

/**
 * Register every admin components & views globally
 * See : https://vuejs.org/guide/components/registration
 */
export default {
    install: (app: App) => {
        // Register views from '../views/Admin'
        app.component('UFAdminActivitiesPage', UFAdminActivitiesPage)
            .component('UFAdminConfigCachePage', UFAdminConfigCachePage)
            .component('UFAdminConfigInfoPage', UFAdminConfigInfoPage)
            .component('UFAdminConfigPage', UFAdminConfigPage)
            .component('UFAdminDashboardPage', UFAdminDashboardPage)
            .component('UFAdminGroupPage', UFAdminGroupPage)
            .component('UFAdminGroupsPage', UFAdminGroupsPage)
            .component('UFAdminPermissionPage', UFAdminPermissionPage)
            .component('UFAdminPermissionsPage', UFAdminPermissionsPage)
            .component('UFAdminRolePage', UFAdminRolePage)
            .component('UFAdminRolesPage', UFAdminRolesPage)
            .component('UFAdminUserPage', UFAdminUserPage)
            .component('UFAdminUsersPage', UFAdminUsersPage)
            // Register components from '../components/Pages/Admin'
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
            .component('UFAdminRoleCreateModal', RoleCreateModal)
            .component('UFAdminRoleDeleteModal', RoleDeleteModal)
            .component('UFAdminRoleEditModal', RoleEditModal)
            .component('UFAdminRoleForm', RoleForm)
            .component('UFAdminRoleInfo', RoleInfo)
            .component('UFAdminRoleManagePermissionModal', RoleManagePermissionModal)
            .component('UFAdminRolePermissions', RolePermissions)
            .component('UFAdminRoleUsers', RoleUsers)
            .component('UFAdminUserActivateModal', UserActivateModal)
            .component('UFAdminUserActivities', UserActivities)
            .component('UFAdminUserCreateModal', UserCreateModal)
            .component('UFAdminUserDeleteModal', UserDeleteModal)
            .component('UFAdminUserEditModal', UserEditModal)
            .component('UFAdminUserForm', UserForm)
            .component('UFAdminUserInfo', UserInfo)
            .component('UFAdminUserManageRolesModal', UserManageRolesModal)
            .component('UFAdminUserPasswordForm', UserPasswordForm)
            .component('UFAdminUserPasswordModal', UserPasswordModal)
            .component('UFAdminUserPasswordResetModal', UserPasswordResetModal)
            .component('UFAdminUserPermissions', UserPermissions)
            .component('UFAdminUserRoles', UserRoles)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        // Views from '../views/Admin'
        UFAdminActivitiesPage: typeof UFAdminActivitiesPage
        UFAdminConfigCachePage: typeof UFAdminConfigCachePage
        UFAdminConfigInfoPage: typeof UFAdminConfigInfoPage
        UFAdminConfigPage: typeof UFAdminConfigPage
        UFAdminDashboardPage: typeof UFAdminDashboardPage
        UFAdminGroupPage: typeof UFAdminGroupPage
        UFAdminGroupsPage: typeof UFAdminGroupsPage
        UFAdminPermissionPage: typeof UFAdminPermissionPage
        UFAdminPermissionsPage: typeof UFAdminPermissionsPage
        UFAdminRolePage: typeof UFAdminRolePage
        UFAdminRolesPage: typeof UFAdminRolesPage
        UFAdminUserPage: typeof UFAdminUserPage
        UFAdminUsersPage: typeof UFAdminUsersPage

        // Components from '../components/Pages/Admin'
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
        UFAdminRoleCreateModal: typeof RoleCreateModal
        UFAdminRoleDeleteModal: typeof RoleDeleteModal
        UFAdminRoleEditModal: typeof RoleEditModal
        UFAdminRoleForm: typeof RoleForm
        UFAdminRoleInfo: typeof RoleInfo
        UFAdminRoleManagePermissionModal: typeof RoleManagePermissionModal
        UFAdminRolePermissions: typeof RolePermissions
        UFAdminRoleUsers: typeof RoleUsers
        UFAdminUserActivateModal: typeof UserActivateModal
        UFAdminUserActivities: typeof UserActivities
        UFAdminUserCreateModal: typeof UserCreateModal
        UFAdminUserDeleteModal: typeof UserDeleteModal
        UFAdminUserEditModal: typeof UserEditModal
        UFAdminUserForm: typeof UserForm
        UFAdminUserInfo: typeof UserInfo
        UFAdminUserManageRolesModal: typeof UserManageRolesModal
        UFAdminUserPasswordForm: typeof UserPasswordForm
        UFAdminUserPasswordModal: typeof UserPasswordModal
        UFAdminUserPasswordResetModal: typeof UserPasswordResetModal
        UFAdminUserPermissions: typeof UserPermissions
        UFAdminUserRoles: typeof UserRoles
    }
}
