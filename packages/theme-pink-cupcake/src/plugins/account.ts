import type { App } from 'vue'
import {
    EmailVerificationRequest,
    PageForgotPassword,
    PageLogin,
    PageRegister,
    PageUserSettings,
    PageUserSettingsEmail,
    PageUserSettingsPassword,
    PageUserSettingsProfile
} from '../views/Account'
import {
    FormEmailVerificationRequest,
    FormEmailVerificationValidation,
    FormForgotPasswordSet,
    FormLogin,
    FormRegister,
    FormUserEmail,
    FormUserPassword,
    FormUserProfile
} from '../components/Pages/Account'

/**
 * Register every Account view components globally
 * See : https://vuejs.org/guide/components/registration
 */
export default {
    install: (app: App) => {
        // Components from ../views/Account
        app.component('UFEmailVerificationRequest', EmailVerificationRequest)
            .component('UFPageForgotPassword', PageForgotPassword)
            .component('UFPageLogin', PageLogin)
            .component('UFPageRegister', PageRegister)
            .component('UFPageUserSettings', PageUserSettings)
            .component('UFPageUserSettingsEmail', PageUserSettingsEmail)
            .component('UFPageUserSettingsPassword', PageUserSettingsPassword)
            .component('UFPageUserSettingsProfile', PageUserSettingsProfile)
            // Components from ../components/Pages/Account
            .component('UFFormEmailVerificationRequest', FormEmailVerificationRequest)
            .component('UFFormEmailVerificationValidation', FormEmailVerificationValidation)
            .component('UFFormForgotPasswordSet', FormForgotPasswordSet)
            .component('UFFormLogin', FormLogin)
            .component('UFFormRegister', FormRegister)
            .component('UFFormUserEmail', FormUserEmail)
            .component('UFFormUserPassword', FormUserPassword)
            .component('UFFormUserProfile', FormUserProfile)
    }
}

declare module 'vue' {
    export interface GlobalComponents {
        UFEmailVerificationRequest: typeof EmailVerificationRequest
        UFPageForgotPassword: typeof PageForgotPassword
        UFPageLogin: typeof PageLogin
        UFPageRegister: typeof PageRegister
        UFPageUserSettings: typeof PageUserSettings
        UFPageUserSettingsEmail: typeof PageUserSettingsEmail
        UFPageUserSettingsPassword: typeof PageUserSettingsPassword
        UFPageUserSettingsProfile: typeof PageUserSettingsProfile
        UFFormEmailVerificationRequest: typeof FormEmailVerificationRequest
        UFFormEmailVerificationValidation: typeof FormEmailVerificationValidation
        UFFormForgotPasswordSet: typeof FormForgotPasswordSet
        UFFormLogin: typeof FormLogin
        UFFormRegister: typeof FormRegister
        UFFormUserEmail: typeof FormUserEmail
        UFFormUserPassword: typeof FormUserPassword
        UFFormUserProfile: typeof FormUserProfile
    }
}
