import type { App } from 'vue'
import type { Router } from 'vue-router'
import { useAuthStore } from './stores/useAuthStore'
import { useAuthGuard } from './guards/authGuard'
import { useAxiosInterceptor } from './composables'

/**
 * Account Sprinkle initialization recipe.
 *
 * This recipe is responsible for running the auth check on load, setting up
 * the router guards and registering the checkAccess as a global properties.
 */
export default {
    install: (app: App, options: { router: Router }) => {
        /**
         * Add Axios error handler.
         * Load first to ensure that all axios requests are intercepted.
         * This is important for the config loading and translator loading.
         */
        useAxiosInterceptor()

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

declare module 'vue' {
    interface ComponentCustomProperties {
        $checkAccess: (slug: string) => boolean
    }
}
