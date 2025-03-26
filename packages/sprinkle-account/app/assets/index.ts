import type { App } from 'vue'
import type { Router } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { useAuthGuard } from './guards/authGuard'

/**
 * Account Sprinkle initialization recipe.
 *
 * This recipe is responsible for running the auth check on load, setting up
 * the router guards and registering the checkAccess as a global properties.
 */
export default {
    install: (app: App, options: { router: Router }) => {
        /**
         * Run auth check on load
         */
        const auth = useAuthStore()
        auth.check()

        /**
         * Setup router guards
         */
        const { router } = options
        useAuthGuard(router)

        /**
         * Register checkAccess as a global property
         */
        app.config.globalProperties.$checkAccess = auth.checkAccess
    }
}
