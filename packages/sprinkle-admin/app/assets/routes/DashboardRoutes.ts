export default [
    {
        path: 'dashboard',
        name: 'admin.dashboard',
        meta: {
            auth: {},
            permission: {
                slug: 'uri_dashboard'
            },
            title: 'DASHBOARD'
        },
        component: () => import('../views/PageDashboard.vue')
    }
]
