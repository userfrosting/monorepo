export default [
    {
        path: 'groups',
        meta: {
            auth: {},
            title: 'GROUP.PAGE',
            description: 'GROUP.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'admin.groups',
                meta: {
                    permission: {
                        slug: 'uri_groups'
                    }
                },
                component: () => import('../views/PageGroups.vue')
            },
            {
                path: 'g/:slug',
                name: 'admin.group',
                meta: {
                    title: 'GROUP.PAGE',
                    description: 'GROUP.INFO_PAGE',
                    permission: {
                        slug: 'uri_group'
                    }
                },
                component: () => import('../views/PageGroup.vue')
            }
        ]
    }
]
