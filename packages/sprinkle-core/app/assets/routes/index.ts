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
        component: () => import('../views/404NotFound.vue')
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'Unauthorized',
        component: () => import('../views/401Unauthorized.vue')
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'Forbidden',
        component: () => import('../views/403Forbidden.vue')
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'Error',
        component: () => import('../views/500ServerError.vue')
    }
]
