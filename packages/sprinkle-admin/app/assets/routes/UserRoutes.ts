export default [
    {
        path: 'users',
        meta: {
            auth: {},
            title: 'USER.PAGE',
            description: 'USER.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'admin.users',
                meta: {
                    permission: {
                        slug: 'uri_users'
                    }
                },
                component: () => import('../views/PageUsers.vue')
            },
            {
                path: 'u/:user_name', // users/u/{user_name}
                name: 'admin.user',
                meta: {
                    title: 'USER.PAGE',
                    description: 'USER.INFO_PAGE',
                    permission: {
                        slug: 'uri_user'
                    }
                },
                component: () => import('../views/PageUser.vue')
            }
        ]
    }
]
