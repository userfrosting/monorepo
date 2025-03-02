export default [
    {
        path: 'activities',
        name: 'admin.activities',
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            title: 'ACTIVITY.PAGE',
            description: 'ACTIVITY.PAGE_DESCRIPTION'
        },
        component: () => import('../views/ActivitiesView.vue')
    }
]
