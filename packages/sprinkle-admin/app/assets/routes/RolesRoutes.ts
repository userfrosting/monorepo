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
                meta: {
                    permission: {
                        slug: 'uri_roles'
                    }
                },
                component: () => import('../views/RolesView.vue')
            },
            {
                path: 'r/:slug', // roles/r/{slug}
                name: 'admin.role',
                meta: {
                    description: 'ROLE.INFO_PAGE',
                    permission: {
                        slug: 'uri_role'
                    }
                },
                component: () => import('../views/RoleView.vue')
            }
        ]
    }
]
