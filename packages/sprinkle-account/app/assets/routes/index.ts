export default [
    {
        path: '/account/sign-in',
        name: 'account.login',
        meta: {
            guest: {
                redirect: { name: 'home' }
            }
        },
        component: () => import('../views/LoginView.vue')
    },
    {
        path: '/account/register',
        name: 'account.register',
        meta: {
            guest: {
                redirect: { name: 'home' }
            }
        },
        component: () => import('../views/RegisterView.vue')
    },
    {
        path: '/account/forgot-password',
        name: 'account.forgot-password',
        meta: {
            guest: {
                redirect: { name: 'home' }
            }
        },
        component: () => import('../views/ForgotPasswordView.vue')
    },
    {
        path: '/account/resend-verification',
        name: 'account.resend-verification',
        meta: {
            guest: {
                redirect: { name: 'home' }
            }
        },
        component: () => import('../views/ResendVerificationView.vue')
    },
    {
        path: '/account/settings',
        name: 'account.settings',
        redirect: { name: 'account.settings.profile' },
        meta: {
            auth: {
                redirect: { name: 'account.login' }
            },
            title: 'ACCOUNT.SETTINGS',
            description: 'ACCOUNT.SETTINGS.DESCRIPTION'
        },
        component: () => import('../views/UserSettings.vue'),
        children: [
            {
                path: 'profile',
                name: 'account.settings.profile',
                meta: {
                    permission: {
                        slug: 'update_account_settings'
                    }
                },
                component: () => import('../views/UserSettingsProfile.vue')
            },
            {
                path: 'password',
                name: 'account.settings.password',
                meta: {
                    permission: {
                        slug: 'update_account_settings'
                    }
                },
                component: () => import('../views/UserSettingsPassword.vue')
            },
            {
                path: 'email',
                name: 'account.settings.email',
                meta: {
                    permission: {
                        slug: 'update_account_settings'
                    }
                },
                component: () => import('../views/UserSettingsEmail.vue')
            }
        ]
    }
]
