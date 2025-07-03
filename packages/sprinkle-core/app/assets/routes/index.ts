/**
 * Default error routes.
 *
 * N.B.: The first of these routes serve as a catch-all, so 404 not found must
 * be first.
 */
export default [
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        meta: {
            title: 'ERROR.404.TITLE',
            description: 'ERROR.404.DESCRIPTION'
        },
        component: () => import('../views/Page404NotFound.vue')
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'Unauthorized',
        meta: {
            title: 'ERROR.401.TITLE',
            description: 'ERROR.401.DESCRIPTION'
        },
        component: () => import('../views/Page401Unauthorized.vue')
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'Forbidden',
        meta: {
            title: 'ERROR.403.TITLE',
            description: 'ERROR.403.DESCRIPTION'
        },
        component: () => import('../views/Page403Forbidden.vue')
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'Error',
        meta: {
            title: 'ERROR.TITLE',
            description: 'ERROR.DESCRIPTION'
        },
        component: () => import('../views/PageError.vue')
    }
]
