import { ref } from 'vue'
import { describe, test, expect, vi, afterEach } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useEmailVerificationApi } from '@userfrosting/sprinkle-account/composables'
import PageEmailVerificationRequest from '../../../views/Account/PageEmailVerificationRequest.vue'
import { CardBoxLarge, CardBox, UFAlert } from '../../../components'

/**
 * Variables - Test email & code vars
 */
const email: string = 'john.doe@example.com'
const code: string = '123456'

/**
 * Mocks - Router, ConfigStore, and EmailVerificationApi
 */
const mockedPush = vi.fn()
vi.mock('vue-router', () => ({
    useRouter: vi.fn(() => ({
        push: mockedPush
    }))
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: vi.fn(() => ({
        get: vi.fn(() => true)
    }))
}))

vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useEmailVerificationApi: vi.fn(() => ({
        apiLoading: false,
        apiError: null,
        submitVerificationCode: vi.fn(() => Promise.resolve()),
        requestVerificationCode: vi.fn(() => Promise.resolve('Verification email sent'))
    }))
}))

/**
 * Global component stubs
 *
 * N.B.: We need to use the real components for the CardBox, as we're testing
 * what's inside them.
 */
config.global.stubs['UFCardBoxLarge'] = CardBoxLarge
config.global.stubs['UFCardBox'] = CardBox
config.global.stubs['UFAlert'] = UFAlert
const wrapperGlobals = {
    global: {
        stubs: ['FontAwesomeIcon', 'router-link']
    }
}

/**
 * Tests
 */
describe('PageEmailVerificationRequest.vue', () => {
    afterEach(() => {
        vi.resetAllMocks()
    })

    test('renders the request step by default', () => {
        const wrapper = mount(PageEmailVerificationRequest, wrapperGlobals)

        // Make sure the component is mounted and the current step is set to 1
        expect(wrapper.exists()).toBe(true)
        // @ts-ignore
        expect(wrapper.vm.currentStep).toBe(1)
        expect(wrapper.text()).toContain('ACCOUNT.VERIFICATION.CODE.IDENTIFY')
    })

    test('moves to validation step after sending request code', async () => {
        // Set mocks for requestVerificationCode
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            requestVerificationCode: vi.fn().mockResolvedValue('Verification email sent'),
            submitVerificationCode: vi.fn(),
            apiLoading: ref(false),
            apiError: ref(null)
        })

        // Mount component, set test email, and emit submit event on the form
        const wrapper = mount(PageEmailVerificationRequest, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.email = email
        const emailForm = wrapper.findComponent({ name: 'FormEmailVerificationRequest' })
        expect(emailForm.exists()).toBe(true)
        await emailForm.vm.$emit('submit')
        await wrapperVm.$nextTick()

        // Make sure the current step is set to 2 (validation step)
        // @ts-ignore
        expect(wrapperVm.currentStep).toBe(2)
        expect(wrapper.text()).toContain('ACCOUNT.VERIFICATION.CODE.ENTER')

        // Make sure the api was called with the correct email and Notification was shown
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.requestVerificationCode
        ).toHaveBeenCalled()
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.requestVerificationCode
        ).toHaveBeenCalledWith(email)
    })

    test('first step handles api errors', async () => {
        // Set mocks for requestVerificationCode
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            requestVerificationCode: vi.fn(),
            submitVerificationCode: vi.fn(),
            apiLoading: ref(false),
            apiError: ref({ title: 'Error', description: 'API error', style: 'Danger' })
        })

        // Mount component, set test email, and emit submit event on the form
        const wrapper = mount(PageEmailVerificationRequest, wrapperGlobals)
        expect(wrapper.text()).toContain('Error API error')
    })

    test('moves to done step after submitting verification code', async () => {
        // Set mocks for requestVerificationCode
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            requestVerificationCode: vi.fn(),
            submitVerificationCode: vi.fn().mockResolvedValue('Account validated'),
            apiLoading: ref(false),
            apiError: ref(null)
        })

        // Mount component and move to validation step
        const wrapper = mount(PageEmailVerificationRequest, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 2
        await wrapperVm.$nextTick()

        // Set the email and code and emit submit event on the validation form
        wrapperVm.email = email
        wrapperVm.code = code
        const validationForm = wrapper.findComponent({ name: 'FormEmailVerificationValidation' })
        expect(validationForm.exists()).toBe(true)
        await validationForm.vm.$emit('submit')
        await wrapperVm.$nextTick()

        // Expect the API call to be made and the result to be handled
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.submitVerificationCode
        ).toHaveBeenCalled()
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.submitVerificationCode
        ).toHaveBeenCalledWith(email, code)

        // Make sure the current step is set to 3 (done step)
        expect(wrapperVm.currentStep).toBe(3)
        expect(wrapper.text()).toContain('ACCOUNT.VERIFICATION.COMPLETE')
    })

    test('second step handles api errors', async () => {
        // Set mocks for requestVerificationCode
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            requestVerificationCode: vi.fn(),
            submitVerificationCode: vi.fn(),
            apiLoading: ref(false),
            apiError: ref({ title: 'Error', description: 'API second error', style: 'Danger' })
        })

        // Mount component, set test email, and emit submit event on the form
        const wrapper = mount(PageEmailVerificationRequest, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 2
        await wrapperVm.$nextTick()
        expect(wrapper.text()).toContain('Error API second error')
    })

    test('resets the form when reset is called', async () => {
        // Set mocks for apiError
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            requestVerificationCode: vi.fn(),
            submitVerificationCode: vi.fn(),
            apiLoading: ref(false),
            apiError: ref(null)
        })

        // Setup wrapper with some data, on step 2
        const wrapper = mount(PageEmailVerificationRequest, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 2
        wrapperVm.email = email
        wrapperVm.code = code
        wrapperVm.apiError = {
            title: 'Error',
            description: 'API error',
            style: 'Danger'
        }
        await wrapperVm.$nextTick()

        const validationForm = wrapper.findComponent({ name: 'FormEmailVerificationValidation' })
        expect(validationForm.exists()).toBe(true)
        await validationForm.vm.$emit('reset')
        expect(wrapperVm.email).toBe('')
        expect(wrapperVm.code).toBe('')
        expect(wrapperVm.currentStep).toBe(1)
    })

    test('redirects to login if email verification is disabled', () => {
        // @ts-ignore
        vi.mocked(useConfigStore).mockReturnValueOnce({
            get: vi.fn(() => false)
        })
        mount(PageEmailVerificationRequest, wrapperGlobals)
        expect(mockedPush).toHaveBeenCalledWith({ name: 'account.login' })
    })
})
