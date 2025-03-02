export default [
    {
        path: 'dashboard',
        name: 'admin.dashboard',
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            title: 'DASHBOARD'
        },
        component: () => import('../views/DashboardView.vue')
    }
]
