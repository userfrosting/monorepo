/**
 * Register every admin components & views globally
 * See : https://vuejs.org/guide/components/registration
 */
import type { App } from 'vue'

/**
 * Register every Account view components globally
 */
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

/**
 * Register every Account Page components globally
 */
import {
    FormEmailVerificationRequest,
    FormForgotPasswordSet,
    FormEmailVerificationValidation,
    FormLogin,
    FormRegister,
    FormUserEmail,
    FormUserPassword,
    FormUserProfile
} from '../components/Pages/Account'

export default {
    install: (app: App) => {
        app.component('UFFormEmailVerificationRequest', FormEmailVerificationRequest)
            .component('UFFormForgotPasswordSet', FormForgotPasswordSet)
            .component('UFFormEmailVerificationValidation', FormEmailVerificationValidation)
            .component('UFFormLogin', FormLogin)
            .component('UFFormRegister', FormRegister)
            .component('UFFormUserEmail', FormUserEmail)
            .component('UFFormUserPassword', FormUserPassword)
            .component('UFFormUserProfile', FormUserProfile)
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
        UFFormEmailVerificationRequest: typeof FormEmailVerificationRequest
        UFFormForgotPasswordSet: typeof FormForgotPasswordSet
        UFFormEmailVerificationValidation: typeof FormEmailVerificationValidation
        UFFormLogin: typeof FormLogin
        UFFormRegister: typeof FormRegister
        UFFormUserEmail: typeof FormUserEmail
        UFFormUserPassword: typeof FormUserPassword
        UFFormUserProfile: typeof FormUserProfile
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
