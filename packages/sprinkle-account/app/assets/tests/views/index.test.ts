import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import {
    ForgotPassword,
    LoginView,
    RegisterView,
    ResendVerificationView
} from '../../views'
import PageUserSettings from '../../views/PageUserSettings.vue'
import PageUserSettingsProfile from '../../views/PageUserSettingsProfile.vue'
import PageUserSettingsPassword from '../../views/PageUserSettingsPassword.vue'
import PageUserSettingsEmail from '../../views/PageUserSettingsEmail.vue'

const marker = (id: string) => ({
    template: `<div data-test="${id}" />`
})

describe('views wrappers', () => {
    test('exports expected views from views/index.ts', () => {
        expect(ForgotPassword).toBeDefined()
        expect(LoginView).toBeDefined()
        expect(RegisterView).toBeDefined()
        expect(ResendVerificationView).toBeDefined()
    })

    test('renders PageLogin wrapper', () => {
        const wrapper = mount(LoginView, {
            global: { stubs: { UFPageLogin: marker('page-login') } }
        })

        expect(wrapper.find('[data-test="page-login"]').exists()).toBe(true)
    })

    test('renders PageRegister wrapper', () => {
        const wrapper = mount(RegisterView, {
            global: { stubs: { UFPageRegister: marker('page-register') } }
        })

        expect(wrapper.find('[data-test="page-register"]').exists()).toBe(true)
    })

    test('renders PageForgotPassword wrapper', () => {
        const wrapper = mount(ForgotPassword, {
            global: { stubs: { UFPageForgotPassword: marker('page-forgot-password') } }
        })

        expect(wrapper.find('[data-test="page-forgot-password"]').exists()).toBe(true)
    })

    test('renders PageResendVerification wrapper', () => {
        const wrapper = mount(ResendVerificationView, {
            global: { stubs: { UFEmailVerificationRequest: marker('page-resend-verification') } }
        })

        expect(wrapper.find('[data-test="page-resend-verification"]').exists()).toBe(true)
    })

    test('renders settings wrappers', () => {
        const settings = mount(PageUserSettings, {
            global: { stubs: { UFPageUserSettings: marker('page-settings') } }
        })
        const profile = mount(PageUserSettingsProfile, {
            global: { stubs: { UFPageUserSettingsProfile: marker('page-settings-profile') } }
        })
        const password = mount(PageUserSettingsPassword, {
            global: { stubs: { UFPageUserSettingsPassword: marker('page-settings-password') } }
        })
        const email = mount(PageUserSettingsEmail, {
            global: { stubs: { UFPageUserSettingsEmail: marker('page-settings-email') } }
        })

        expect(settings.find('[data-test="page-settings"]').exists()).toBe(true)
        expect(profile.find('[data-test="page-settings-profile"]').exists()).toBe(true)
        expect(password.find('[data-test="page-settings-password"]').exists()).toBe(true)
        expect(email.find('[data-test="page-settings-email"]').exists()).toBe(true)
    })
})
