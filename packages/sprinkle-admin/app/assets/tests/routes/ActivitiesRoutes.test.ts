import { describe, expect, test } from 'vitest'
import AdminActivitiesRoutes from '../../routes/ActivitiesRoutes'

describe('routes/ActivitiesRoutes.ts', () => {
    test('defines the activities route metadata and lazy component', async () => {
        expect(AdminActivitiesRoutes).toHaveLength(1)

        const activities = AdminActivitiesRoutes[0]

        expect(activities.path).toBe('activities')
        expect(activities.name).toBe('admin.activities')
        expect(activities.meta).toEqual({
            auth: {},
            permission: {
                slug: 'uri_activities'
            },
            title: 'ACTIVITY.PAGE',
            description: 'ACTIVITY.PAGE_DESCRIPTION'
        })

        const module = await activities.component?.()
        expect(module?.default).toBeDefined()
    })
})
