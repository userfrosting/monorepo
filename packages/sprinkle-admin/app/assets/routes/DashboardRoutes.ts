export default [
    {
        path: 'dashboard',
        name: 'admin.dashboard',
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            permission: {
                slug: 'uri_dashboard'
            },
            title: 'DASHBOARD'
        },
        component: () => import('../views/DashboardView.vue')
    }
]
