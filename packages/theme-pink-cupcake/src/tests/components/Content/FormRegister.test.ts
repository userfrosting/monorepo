import { mount, config } from '@vue/test-utils'
import { describe, test, expect, vi, afterEach } from 'vitest'
import UIkit from 'uikit'
import type { UserInterface, RegisterRequest } from '@userfrosting/sprinkle-account/interfaces'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import FormRegister from '../../../components/Pages/Account/FormRegister.vue'
import UFAlert from '../../../components/UFAlert.vue'

// Register the UFAlert component stub globally
config.global.stubs['UFAlert'] = UFAlert
config.global.stubs['FontAwesomeIcon'] = { template: '<span></span>' }

// Mock default form
const defaultForm: RegisterRequest = {
    first_name: '',
    last_name: '',
    email: '',
    user_name: '',
    password: '',
    passwordc: '',
    locale: 'en_US',
    captcha: '',
    spiderbro: 'http://'
}

// Mock available locales
const availableLocales = {
    en_US: 'English',
    fr_FR: 'French'
}

// Test user data
const testUser: UserInterface = {
    id: 1,
    user_name: 'JohnDoe',
    first_name: 'John',
    last_name: 'Doe',
    full_name: 'John Doe',
    email: 'john.doe@example.com',
    avatar: '',
    flag_enabled: true,
    flag_verified: true,
    group_id: null,
    locale: 'fr_FR',
    created_at: '',
    updated_at: '',
    deleted_at: null
}

// Completed form data
const testForm: RegisterRequest = {
    first_name: 'John',
    last_name: 'Doe',
    email: 'john.doe@example.com',
    user_name: 'JohnDoe',
    password: 'password',
    passwordc: 'password',
    locale: 'fr_FR',
    captcha: 'captcha',
    spiderbro: 'http://'
}

// UiKit notification data
const uikitNotification = {
    message: 'Succesfully registered John Doe!',
    status: 'success',
    pos: 'top-right',
    timeout: 4000
}

// Mock the register composable
const mockedSubmitRegistration = vi.fn()
vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useRegisterApi: () => ({
        defaultRegistrationForm: vi.fn(() => defaultForm),
        availableLocales: vi.fn(() => availableLocales),
        captchaUrl: vi.fn(() => '/account/captcha'),
        submitRegistration: mockedSubmitRegistration
    })
}))

// Mock the config & translator store
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: () => ({
        get: vi.fn(() => 'Site Title')
    }),
    useTranslator: () => ({
        translate: vi.fn(() => '')
    })
}))

// Mock the router
const mockedRouterPush = vi.fn()
vi.mock('vue-router', () => ({
    useRouter: () => ({
        push: mockedRouterPush
    })
}))

describe('FormRegister.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    test('renders correctly', () => {
        const wrapper = mount(FormRegister)
        expect(wrapper.exists()).toBe(true)
    })

    test('handles successful register', async () => {
        vi.mocked(mockedSubmitRegistration).mockResolvedValueOnce({
            user: testUser,
            message: 'Succesfully registered John Doe!'
        })
        vi.spyOn(UIkit, 'notification')

        const wrapper = mount(FormRegister)
        // @ts-ignore
        wrapper.vm.formData = testForm
        await (wrapper.vm as any).submitForm()

        // Spy on the authStore & UIkit notification method
        expect(mockedSubmitRegistration).toHaveBeenCalledTimes(1)
        expect(mockedSubmitRegistration).toHaveBeenCalledWith(testForm)
        expect(UIkit.notification).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledWith(uikitNotification)
    })

    test('handles registration failure', async () => {
        const mockError: AlertInterface = { title: 'Invalid credentials' }
        vi.mocked(mockedSubmitRegistration).mockRejectedValueOnce(mockError)
        vi.spyOn(UIkit, 'notification')

        const wrapper = mount(FormRegister)
        // @ts-ignore
        wrapper.vm.formData = testForm
        await (wrapper.vm as any).submitForm()

        // Spy on the authStore & UIkit notification method
        expect(mockedSubmitRegistration).toHaveBeenCalledTimes(1)
        expect(mockedSubmitRegistration).toHaveBeenCalledWith(testForm)
        expect(UIkit.notification).not.toHaveBeenCalled()
        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch('Invalid credentials')
    })

    test('Handle form using the v-model', async () => {
        vi.mocked(mockedSubmitRegistration).mockResolvedValueOnce({
            user: testUser,
            message: 'Succesfully registered John Doe!'
        })
        vi.spyOn(UIkit, 'notification')

        const wrapper = mount(FormRegister)

        // Set forms values
        wrapper.find('[data-test="first_name"]').setValue(testForm.first_name)
        wrapper.find('[data-test="last_name"]').setValue(testForm.last_name)
        wrapper.find('[data-test="email"]').setValue(testForm.email)
        wrapper.find('[data-test="username"]').setValue(testForm.user_name)
        wrapper.find('[data-test="password"]').setValue(testForm.password)
        wrapper.find('[data-test="passwordc"]').setValue(testForm.passwordc)
        wrapper.find('[data-test="captcha"]').setValue(testForm.captcha)
        wrapper.find('[data-test="locale"]').setValue(testForm.locale)

        // Submit form
        await wrapper.find('form').trigger('submit')

        // Spy on the authStore & UIkit notification method
        expect(mockedSubmitRegistration).toHaveBeenCalledTimes(1)
        expect(mockedSubmitRegistration).toHaveBeenCalledWith(testForm)
        expect(UIkit.notification).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledWith(uikitNotification)
    })
})
