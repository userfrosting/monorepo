export default [
    {
        path: 'config',
        name: 'admin.config',
        redirect: { name: 'admin.config.info' },
        meta: {
            auth: {},
            title: 'SITE_CONFIG',
            description: 'SITE_CONFIG.PAGE_DESCRIPTION'
        },
        component: () => import('../views/PageConfig.vue'),
        children: [
            {
                path: 'info',
                name: 'admin.config.info',
                meta: {
                    permission: {
                        slug: 'view_system_info'
                    }
                },
                component: () => import('../views/PageConfigInfo.vue')
            },
            {
                path: 'cache',
                name: 'admin.config.cache',
                meta: {
                    permission: {
                        slug: 'clear_cache'
                    }
                },
                component: () => import('../views/PageConfigCache.vue')
            }
        ]
    }
]
