import AccountRoutes from '@userfrosting/sprinkle-account/routes'
import AdminRoutes from '@userfrosting/sprinkle-admin/routes'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '',
            redirect: { name: 'home' },
            component: () => import('../layouts/LayoutPage.vue'),
            children: [
                {
                    path: '/',
                    name: 'home',
                    component: () => import('../views/HomeView.vue')
                },
                {
                    path: '/about',
                    name: 'about',
                    meta: {
                        auth: {
                            redirect: { name: 'account.login' }
                        }
                    },
                    component: () => import('../views/AboutView.vue')
                },
                // Include sprinkles routes
                ...AccountRoutes
            ]
        },
        {
            path: '',
            component: () => import('../layouts/LayoutDashboard.vue'),
            children: [
                ...AdminRoutes,
                {
                    path: '/test',
                    meta: {
                        auth: {
                            redirect: { name: 'account.login' }
                        }
                    },
                    children: [
                        {
                            path: '',
                            name: 'test',
                            component: () => import('../views/TestView.vue'),
                        },
                        {
                            path: 't/:slug',
                            name: 'test.slug',
                            component: () => import('../views/TestViewDetail.vue')
                        }
                    ]
                }
            ]
        }
    ]
})

export default router
