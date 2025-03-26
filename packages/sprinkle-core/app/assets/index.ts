import type { App } from 'vue'
import { useConfigStore, useTranslator } from './stores'

/**
 * Core Sprinkle initialization recipe.
 *
 * This recipe is responsible for loading the configuration from the api,
 * loading the translations and register the translator as $t and $tdate global
 * properties.
 */
export default {
    install: (app: App) => {
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
    }
}
