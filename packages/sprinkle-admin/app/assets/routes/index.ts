import AdminActivitiesRoutes from './ActivitiesRoutes'
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
    ...AdminUsersRoutes
]

export default AdminRoutes

export {
    AdminDashboardRoutes,
    AdminActivitiesRoutes,
    AdminGroupsRoutes,
    AdminPermissionsRoutes,
    AdminRolesRoutes,
    AdminUsersRoutes
}
