import { ref } from 'vue'
import { describe, expect, vi, beforeEach, test } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import FormRegister from '../../../../components/Pages/Account/FormRegister.vue'
import UFAlert from '../../../../components/UFAlert.vue'
import UFModal from '../../../../components/Modals/UFModal.vue'
import FaCode from '../../../../components/Content/2FaCode.vue'

const { mockAxiosGet, mockedRouterPush } = vi.hoisted(() => ({
    mockAxiosGet: vi.fn((url: string) => {
        if (url === '/c/tos') {
            return Promise.resolve({ data: 'TOS...' })
        }
        if (url === '/c/privacy') {
            return Promise.resolve({ data: 'PRIVACY...' })
        }
        return Promise.resolve({ data: {} })
    }),
    mockedRouterPush: vi.fn()
}))

const { mockSubmitRegistration, mockSubmitVerificationCode, mockSuggestUsername } = vi.hoisted(
    () => ({
        mockSubmitRegistration: vi.fn().mockResolvedValue(undefined),
        mockSubmitVerificationCode: vi.fn().mockResolvedValue(undefined),
        mockSuggestUsername: vi.fn().mockResolvedValue('suggestedUser')
    })
)

vi.mock('axios', () => ({
    default: {
        get: mockAxiosGet
    }
}))

// Mock composables and dependencies
vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useRegisterApi: () => ({
        submitRegistration: mockSubmitRegistration,
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
        suggestUsername: mockSuggestUsername
    }),
    useEmailVerificationApi: () => ({
        apiLoading: ref(false),
        apiError: ref(null),
        submitVerificationCode: mockSubmitVerificationCode
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
        push: mockedRouterPush
    })
}))

describe('FormRegister.vue', () => {
    let wrapper: any

    beforeEach(() => {
        mockedRouterPush.mockReset()
        mockSubmitRegistration.mockReset().mockResolvedValue(undefined)
        mockSubmitVerificationCode.mockReset().mockResolvedValue(undefined)
        mockSuggestUsername.mockReset().mockResolvedValue('suggestedUser')
        mockAxiosGet.mockImplementation((url: string) => {
            if (url === '/c/tos') {
                return Promise.resolve({ data: 'TOS...' })
            }
            if (url === '/c/privacy') {
                return Promise.resolve({ data: 'PRIVACY...' })
            }
            return Promise.resolve({ data: {} })
        })

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
        // Assert initial model values
        expect(wrapper.vm.formData.locale).toBe('en')
        expect(wrapper.vm.formData.captcha).toBe('')

        await wrapper.find('input[data-test="first_name"]').setValue('John')
        await wrapper.find('input[data-test="last_name"]').setValue('Doe')
        await wrapper.find('input[data-test="email"]').setValue('john@example.com')
        await wrapper.find('input[data-test="username"]').setValue('johndoe')
        await wrapper.find('input[data-test="password"]').setValue('password123')
        await wrapper.find('input[data-test="passwordc"]').setValue('password123')
        await wrapper.find('select[data-test="locale"]').setValue('fr')
        await wrapper.find('input[data-test="captcha"]').setValue('captchaCode')
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(true)

        // Assert final model values after submission
        expect(wrapper.vm.formData.locale).toBe('fr')
        expect(wrapper.vm.formData.captcha).toBe('captchaCode')
    })

    test('disables submit button when loading', async () => {
        wrapper.vm.apiLoading = true
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[data-test="submit"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="submit"]').text()).toBe('REGISTER_ME')
        expect(wrapper.find('[data-test="submit"]').attributes().disabled).toBeDefined()
    })

    test('disables buttons in verification when loading', async () => {
        wrapper.vm.displayVerification = true
        wrapper.vm.verificationApiLoading = true
        await wrapper.vm.$nextTick()

        expect(wrapper.find('[data-test="submitVerification"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="submitVerification"]').attributes().disabled).toBeDefined()
        expect(wrapper.find('[data-test="tryAgain"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="tryAgain"]').attributes().disabled).toBeDefined()
    })

    test('updates the verification code through FaCode v-model', async () => {
        wrapper.vm.displayVerification = true
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.code).toBe('')

        const childWrapper = wrapper.findComponent(FaCode)
        expect(childWrapper.exists()).toBe(true)
        await childWrapper.get('input').setValue('123456')
        await flushPromises()

        expect(wrapper.vm.code).toBe('123456')
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
        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(true)
        expect(mockedRouterPush).toHaveBeenCalledWith({ name: 'account.login' })
    })

    test('submits verification code (on submit click) and redirects', async () => {
        // Show verification form
        wrapper.vm.displayVerification = true
        await wrapper.vm.$nextTick()
        await wrapper.find('button[data-test="submitVerification"]').trigger('click')
        await flushPromises()
        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(true)
        expect(mockedRouterPush).toHaveBeenCalledWith({ name: 'account.login' })
    })

    test('does not switch to verification form when validation is invalid', async () => {
        wrapper.vm.r$.$validate.mockResolvedValueOnce({ valid: false })
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(false)
    })

    test('does not switch to verification form when registration fails', async () => {
        mockSubmitRegistration.mockRejectedValueOnce(new Error('registration failed'))
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()

        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(false)
        expect(mockSubmitRegistration).toHaveBeenCalled()
    })

    test('shows verification error and supports try-again navigation', async () => {
        wrapper.vm.displayVerification = true
        wrapper.vm.verificationApiError = {
            title: 'Verification error',
            description: 'Invalid code',
            style: 'Danger',
            closeBtn: true
        }
        await wrapper.vm.$nextTick()

        expect(wrapper.find('[data-test="verificationError"]').exists()).toBe(true)
        await wrapper.find('[data-test="tryAgain"]').trigger('click')
        expect(mockedRouterPush).toHaveBeenCalledWith({ name: 'account.verification' })
    })

    test('handles tos/privacy fetch failures gracefully on mount', async () => {
        mockAxiosGet.mockRejectedValue(new Error('content fetch failed'))
        const failedWrapper = mount(FormRegister, {
            global: {
                stubs: {
                    UFAlert: UFAlert,
                    UFModal: UFModal,
                    UFFormValidationError: { template: '<span></span>' },
                    FontAwesomeIcon: { template: '<span></span>' }
                }
            }
        })
        await flushPromises()

        expect(failedWrapper.exists()).toBe(true)
        expect(failedWrapper.find('#show-tos').exists()).toBe(true)
        expect(failedWrapper.find('#show-privacy').exists()).toBe(true)
    })
})
