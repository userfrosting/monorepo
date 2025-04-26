/**
 * Register every admin components & views globally
 * See : https://vuejs.org/guide/components/registration
 */
import type { App } from 'vue'
import {
    PageLogin,
    PageRegister,
    PageForgotPassword,
    EmailVerificationRequest,
    PageUserSettings,
    PageUserSettingsProfile,
    PageUserSettingsPassword,
    PageUserSettingsEmail
} from '../views/Account'
import {
    FormLogin,
    FormRegister,
    FormForgotPassword,
    FormEmailVerificationRequest
} from '../components/Pages/Account'

export default {
    install: (app: App) => {
        app.component('UFFormLogin', FormLogin)
            .component('UFFormRegister', FormRegister)
            .component('UFFormForgotPassword', FormForgotPassword)
            .component('UFFormEmailVerificationRequest', FormEmailVerificationRequest)
            .component('UFPageLogin', PageLogin)
            .component('UFPageRegister', PageRegister)
            .component('UFPageForgotPassword', PageForgotPassword)
            .component('UFEmailVerificationRequest', EmailVerificationRequest)
            .component('UFPageUserSettings', PageUserSettings)
            .component('UFPageUserSettingsProfile', PageUserSettingsProfile)
            .component('UFPageUserSettingsPassword', PageUserSettingsPassword)
            .component('UFPageUserSettingsEmail', PageUserSettingsEmail)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        UFFormLogin: typeof FormLogin
        UFFormRegister: typeof FormRegister
        UFFormForgotPassword: typeof FormForgotPassword
        UFFormEmailVerificationRequest: typeof FormEmailVerificationRequest
        UFPageLogin: typeof PageLogin
        UFPageRegister: typeof PageRegister
        UFPageForgotPassword: typeof PageForgotPassword
        UFEmailVerificationRequest: typeof EmailVerificationRequest
        UFPageUserSettings: typeof PageUserSettings
        UFPageUserSettingsProfile: typeof PageUserSettingsProfile
        UFPageUserSettingsPassword: typeof PageUserSettingsPassword
        UFPageUserSettingsEmail: typeof PageUserSettingsEmail
    }
}
