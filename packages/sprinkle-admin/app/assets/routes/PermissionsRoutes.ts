export default [
    {
        path: 'permissions',
        meta: {
            permission: {
                slug: 'uri_permissions'
            },
            auth: {},
            title: 'PERMISSION.PAGE',
            description: 'PERMISSION.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'admin.permissions',
                component: () => import('../views/PagePermissions.vue')
            },
            {
                path: 'p/:id', // permissions/p/{id}
                name: 'admin.permission',
                component: () => import('../views/PagePermission.vue'),
                meta: {
                    description: 'PERMISSION.INFO_PAGE'
                }
            }
        ]
    }
]
