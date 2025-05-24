import type { App } from 'vue'
import { ErrorPage } from '../views/Core'

/**
 * Register every Core components & views globally
 * See : https://vuejs.org/guide/components/registration
 */
export default {
    install: (app: App) => {
        app.component('UFErrorPage', ErrorPage)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        UFErrorPage: typeof ErrorPage
    }
}
