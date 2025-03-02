export default [
    {
        path: 'groups',
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            title: 'GROUP.PAGE',
            description: 'GROUP.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'admin.groups',
                component: () => import('../views/GroupsView.vue')
            },
            {
                path: 'g/:slug',
                name: 'admin.group',
                component: () => import('../views/GroupView.vue'),
                meta: {
                    description: 'GROUP.INFO_PAGE'
                }
            }
        ]
    }
]
