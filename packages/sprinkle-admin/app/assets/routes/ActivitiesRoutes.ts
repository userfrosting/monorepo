export default [
    {
        path: 'activities',
        name: 'admin.activities',
        meta: {
            auth: {},
            permission: {
                slug: 'uri_activities'
            },
            title: 'ACTIVITY.PAGE',
            description: 'ACTIVITY.PAGE_DESCRIPTION'
        },
        component: () => import('../views/PageActivities.vue')
    }
]
