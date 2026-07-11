import { describe, expect, test } from 'vitest'
import AdminRoutes, {
    AdminActivitiesRoutes,
    AdminConfigRoutes,
    AdminDashboardRoutes,
    AdminGroupsRoutes,
    AdminPermissionsRoutes,
    AdminRolesRoutes,
    AdminUsersRoutes
} from '../../routes'

describe('routes/index.ts', () => {
    test('composes admin route groups in expected order', () => {
        expect(AdminRoutes).toHaveLength(8)
        expect(AdminRoutes[0]).toEqual({ path: '', redirect: { name: 'admin.dashboard' } })

        expect(AdminRoutes[1]).toEqual(AdminDashboardRoutes[0])
        expect(AdminRoutes[2]).toEqual(AdminActivitiesRoutes[0])
        expect(AdminRoutes[3]).toEqual(AdminGroupsRoutes[0])
        expect(AdminRoutes[4]).toEqual(AdminPermissionsRoutes[0])
        expect(AdminRoutes[5]).toEqual(AdminRolesRoutes[0])
        expect(AdminRoutes[6]).toEqual(AdminUsersRoutes[0])
        expect(AdminRoutes[7]).toEqual(AdminConfigRoutes[0])
    })

    test('contains expected metadata and names for key routes', () => {
        const dashboard = AdminDashboardRoutes[0]
        const activities = AdminActivitiesRoutes[0]
        const config = AdminConfigRoutes[0]

        expect(dashboard.name).toBe('admin.dashboard')
        expect(dashboard.meta?.permission?.slug).toBe('uri_dashboard')

        expect(activities.name).toBe('admin.activities')
        expect(activities.meta?.permission?.slug).toBe('uri_activities')

        expect(config.name).toBe('admin.config')
        expect(config.redirect).toEqual({ name: 'admin.config.info' })
        expect(config.children).toHaveLength(2)
        expect(config.children?.[0].name).toBe('admin.config.info')
        expect(config.children?.[1].name).toBe('admin.config.cache')
    })

    test('loads all lazy route components', async () => {
        const topRoutes = [
            ...AdminDashboardRoutes,
            ...AdminActivitiesRoutes,
            ...AdminGroupsRoutes,
            ...AdminPermissionsRoutes,
            ...AdminRolesRoutes,
            ...AdminUsersRoutes,
            ...AdminConfigRoutes
        ]

        const loaders = topRoutes.flatMap((route) => {
            const r = route as any
            const routeLoaders = r.component ? [r.component] : []
            const childLoaders = r.children?.flatMap((child: any) =>
                child.component ? [child.component] : []
            )

            return [...routeLoaders, ...(childLoaders ?? [])]
        })

        const modules = await Promise.all(loaders.map((load) => load()))

        for (const module of modules) {
            expect(module).toBeDefined()
            expect(module.default).toBeDefined()
        }
    })
})
