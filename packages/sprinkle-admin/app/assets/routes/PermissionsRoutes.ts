export default [
    {
        path: 'permissions',
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            title: 'PERMISSION.PAGE',
            description: 'PERMISSION.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'admin.permissions',
                component: () => import('../views/PermissionsView.vue')
            },
            {
                path: 'p/:id', // permissions/p/{id}
                name: 'admin.permission',
                component: () => import('../views/PermissionView.vue'),
                meta: {
                    description: 'PERMISSION.INFO_PAGE'
                }
            }
        ]
    }
]
