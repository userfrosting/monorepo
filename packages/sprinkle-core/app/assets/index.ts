import type { App } from 'vue'
import { useConfigStore, useTranslator } from './stores'
import { useCsrf } from './composables/useCsrf'
import { useAxiosInterceptor } from './composables'

/**
 * Core Sprinkle initialization recipe.
 *
 * This recipe is responsible for loading the configuration from the api,
 * loading the translations, register the translator as $t and $tdate global
 * properties and setting up the axios CSRF headers.
 */
export default {
    install: (app: App) => {
        /**
         * Add Axios error handler.
         * Load first to ensure that all axios requests are intercepted.
         * This is important for the config loading and translator loading.
         */
        useAxiosInterceptor()

        /**
         * Load configuration
         */
        useConfigStore().load()

        /**
         * Load translations & add $t+$tdate to global properties
         */
        const translator = useTranslator()
        translator.load()
        app.config.globalProperties.$t = translator.translate
        app.config.globalProperties.$tdate = translator.translateDate

        /**
         * Setup CSRF Protection.
         */
        useCsrf()
    }
}
