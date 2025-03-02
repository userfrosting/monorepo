export default [
    {
        path: 'roles',
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            title: 'ROLE.PAGE',
            description: 'ROLE.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'admin.roles',
                component: () => import('../views/RolesView.vue')
            },
            {
                path: 'r/:slug', // roles/r/{slug}
                name: 'admin.role',
                component: () => import('../views/RoleView.vue'),
                meta: {
                    description: 'ROLE.INFO_PAGE'
                }
            }
        ]
    }
]
