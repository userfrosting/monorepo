import AdminActivitiesRoutes from './ActivitiesRoutes'
import AdminConfigRoutes from './ConfigRoutes'
import AdminDashboardRoutes from './DashboardRoutes'
import AdminGroupsRoutes from './GroupsRoutes'
import AdminPermissionsRoutes from './PermissionsRoutes'
import AdminRolesRoutes from './RolesRoutes'
import AdminUsersRoutes from './UserRoutes'

const AdminRoutes = [
    { path: '', redirect: { name: 'admin.dashboard' } },
    ...AdminDashboardRoutes,
    ...AdminActivitiesRoutes,
    ...AdminGroupsRoutes,
    ...AdminPermissionsRoutes,
    ...AdminRolesRoutes,
    ...AdminUsersRoutes,
    ...AdminConfigRoutes
]

export default AdminRoutes

export {
    AdminDashboardRoutes,
    AdminActivitiesRoutes,
    AdminGroupsRoutes,
    AdminPermissionsRoutes,
    AdminRolesRoutes,
    AdminUsersRoutes,
    AdminConfigRoutes
}
