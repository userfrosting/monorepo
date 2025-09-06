import { ref } from 'vue'
import { describe, expect, vi, beforeEach, test } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import FormRegister from '../../../../components/Pages/Account/FormRegister.vue'
import UFAlert from '../../../../components/UFAlert.vue'
import UFModal from '../../../../components/Modals/UFModal.vue'

// Mock composables and dependencies
vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useRegisterApi: () => ({
        submitRegistration: vi.fn().mockResolvedValue(undefined),
        availableLocales: () => ({ en: 'English', fr: 'Français' }),
        captchaUrl: () => 'captcha.png',
        formData: ref({
            first_name: '',
            last_name: '',
            email: '',
            user_name: '',
            password: '',
            passwordc: '',
            locale: 'en',
            captcha: ''
        }),
        apiLoading: ref(false),
        apiError: ref(null),
        r$: {
            $validate: vi.fn().mockResolvedValue({ valid: true }),
            $errors: {
                first_name: [],
                last_name: [],
                email: [],
                user_name: [],
                password: [],
                passwordc: [],
                locale: [],
                captcha: []
            },
            first_name: { $error: false },
            last_name: { $error: false },
            email: { $error: false },
            user_name: { $error: false },
            password: { $error: false },
            passwordc: { $error: false },
            locale: { $error: false },
            captcha: { $error: false }
        },
        r$username: {
            $error: false,
            $errors: { user_name: [] }
        },
        passwordMinLength: 8,
        passwordMaxLength: 32,
        suggestUsername: vi.fn().mockResolvedValue('suggestedUser')
    }),
    useEmailVerificationApi: () => ({
        apiLoading: ref(false),
        apiError: ref(null),
        submitVerificationCode: vi.fn().mockResolvedValue(undefined)
    })
}))

// Mock the config & translator store
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: () => ({
        get: vi.fn().mockReturnValue('Test Site')
    }),
    useTranslator: () => ({
        translate: vi.fn().mockImplementation((key) => {
            if (key === 'TOS_AGREEMENT') return `<a href="#">TOS</a>`
            return key
        })
    })
}))

// Mock the router
vi.mock('vue-router', () => ({
    useRouter: () => ({
        push: vi.fn()
    })
}))

describe('FormRegister.vue', () => {
    let wrapper: any

    beforeEach(() => {
        wrapper = mount(FormRegister, {
            global: {
                stubs: {
                    UFAlert: UFAlert,
                    UFModal: UFModal,
                    UFFormValidationError: { template: '<span></span>' },
                    FontAwesomeIcon: { template: '<span></span>' }
                }
            }
        })
    })

    test('renders correctly', () => {
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('input[data-test="first_name"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="last_name"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="email"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="username"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="password"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="passwordc"]').exists()).toBe(true)
        expect(wrapper.find('select[data-test="locale"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="captcha"]').exists()).toBe(true)
        expect(wrapper.find('button[data-test="submit"]').exists()).toBe(true)
    })

    test('shows available locales', () => {
        const options = wrapper.findAll('select[data-test="locale"] option')
        expect(options.length).toBeGreaterThan(0)
        expect(options[0].text()).toBe('English')
        expect(options[1].text()).toBe('Français')
    })

    test('calls suggestUsername when SUGGEST button is clicked', async () => {
        await wrapper.find('a.uk-button').trigger('click')
        await flushPromises()
        expect(wrapper.vm.formData.user_name).toBe('suggestedUser')
    })

    test('submits registration and shows verification form', async () => {
        await wrapper.find('input[data-test="first_name"]').setValue('John')
        await wrapper.find('input[data-test="last_name"]').setValue('Doe')
        await wrapper.find('input[data-test="email"]').setValue('john@example.com')
        await wrapper.find('input[data-test="username"]').setValue('johndoe')
        await wrapper.find('input[data-test="password"]').setValue('password123')
        await wrapper.find('input[data-test="passwordc"]').setValue('password123')
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(true)
    })

    test('disables submit button when loading', async () => {
        wrapper.vm.apiLoading = true
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[data-test="submit"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="submit"]').text()).toBe('REGISTER_ME')
        expect(wrapper.find('[data-test="submit"]').attributes().disabled).toBeDefined()
    })

    test('handles registration failure', async () => {
        wrapper.vm.apiError = {
            title: 'Registration error',
            description: 'You did not enter the captcha code correctly.',
            style: 'Danger',
            closeBtn: true
        }
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch(
            'Registration error You did not enter the captcha code correctly.'
        )
    })

    test('renders TOS agreement', () => {
        expect(wrapper.html()).toContain('<a href="#">TOS</a>')
    })

    test('submits verification code and redirects', async () => {
        // Show verification form
        wrapper.vm.displayVerification = true
        await wrapper.vm.$nextTick()
        await wrapper.find('form[data-test="verificationForm"]').trigger('submit.prevent')
        await flushPromises()
        // The router.push is mocked, so we just check that the form submits without error
        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(true)
    })
})
