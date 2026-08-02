import { ref } from 'vue'
import { describe, test, expect, vi, afterEach } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { useForgotPasswordApi } from '@userfrosting/sprinkle-account/composables'
import PageForgotPassword from '../../../views/Account/PageForgotPassword.vue'
import { CardBoxLarge, CardBox, UFAlert } from '../../../components'

/**
 * Variables - Test email & code vars
 */
const email: string = 'john.doe@example.com'
const code: string = '123456'
const password: string = 'password123'
const passwordConfirm: string = 'password123'

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
    useForgotPasswordApi: vi.fn(() => ({
        apiLoading: false,
        apiError: null,
        setPassword: vi.fn(() => Promise.resolve()),
        requestCode: vi.fn(() => Promise.resolve('Verification email sent'))
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
        stubs: {
            FontAwesomeIcon: { template: '<i />' },
            'router-link': { template: '<a><slot /></a>' }
        }
    }
}

/**
 * Tests
 */
describe('PageForgotPassword.vue', () => {
    afterEach(() => {
        vi.resetAllMocks()
    })

    test('renders the request step by default', () => {
        const wrapper = mount(PageForgotPassword, wrapperGlobals)

        // Make sure the component is mounted and the current step is set to 1
        expect(wrapper.exists()).toBe(true)
        // @ts-expect-error : currentStep is defined in the component but TS doesn't see it
        expect(wrapper.vm.currentStep).toBe(1)
        expect(wrapper.text()).toContain('ACCOUNT.VERIFICATION.CODE.IDENTIFY')
    })

    test('moves to validation step after sending request code', async () => {
        // Set mocks for requestCode
        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode: vi.fn().mockResolvedValue('Verification email sent'),
            setPassword: vi.fn(),
            apiLoading: ref(false),
            apiError: ref(null)
        })

        // Mount component, set test email, and emit submit event on the form
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.email = email
        const emailForm = wrapper.findComponent({ name: 'FormEmailVerificationRequest' })
        expect(emailForm.exists()).toBe(true)
        await emailForm.vm.$emit('update:modelValue', email)
        await emailForm.vm.$emit('submit')
        await wrapperVm.$nextTick()

        // Make sure the current step is set to 2 (validation step)
        expect(wrapperVm.currentStep).toBe(2)
        expect(wrapper.text()).toContain('ACCOUNT.VERIFICATION.CODE.ENTER')

        // Make sure the api was called with the correct email and Notification was shown
        expect(vi.mocked(useForgotPasswordApi).mock.results[0].value.requestCode).toHaveBeenCalled()
        expect(
            vi.mocked(useForgotPasswordApi).mock.results[0].value.requestCode
        ).toHaveBeenCalledWith(email)
    })

    test('first step handles api errors', async () => {
        // Set mocks for requestCode
        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode: vi.fn(),
            setPassword: vi.fn(),
            apiLoading: ref(false),
            apiError: ref({ title: 'Error', description: 'API error', style: 'Danger' })
        })

        // Mount component, set test email, and emit submit event on the form
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        expect(wrapper.text()).toContain('Error API error')
    })

    test('moves to set password step after submitting verification code', async () => {
        // Mount component and move to validation step
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 2
        await wrapperVm.$nextTick()

        // Set the email and code and emit submit event on the validation form
        wrapperVm.email = email
        wrapperVm.code = code
        const validationForm = wrapper.findComponent({ name: 'FormEmailVerificationValidation' })
        expect(validationForm.exists()).toBe(true)
        await validationForm.vm.$emit('update:modelValue', code)
        await validationForm.vm.$emit('submit')
        await wrapperVm.$nextTick()

        // Make sure the current step is set to 3 (set password step)
        expect(wrapperVm.currentStep).toBe(3)
    })

    test('moves to done step after submitting new passwords', async () => {
        // Set mocks for requestCode
        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode: vi.fn(),
            setPassword: vi.fn().mockResolvedValue('Password updated'),
            apiLoading: ref(false),
            apiError: ref(null)
        })

        // Mount component and move to validation step
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 3
        await wrapperVm.$nextTick()

        // Set the email and code and emit submit event on the validation form
        wrapperVm.email = email
        wrapperVm.code = code
        wrapperVm.password = password
        wrapperVm.passwordc = passwordConfirm
        const forgotPasswordForm = wrapper.findComponent({ name: 'FormForgotPasswordSet' })
        expect(forgotPasswordForm.exists()).toBe(true)
        await forgotPasswordForm.vm.$emit('update:password', password)
        await forgotPasswordForm.vm.$emit('update:passwordc', passwordConfirm)
        await forgotPasswordForm.vm.$emit('submit')
        await wrapperVm.$nextTick()

        // Expect the API call to be made and the result to be handled
        expect(vi.mocked(useForgotPasswordApi).mock.results[0].value.setPassword).toHaveBeenCalled()
        expect(
            vi.mocked(useForgotPasswordApi).mock.results[0].value.setPassword
        ).toHaveBeenCalledWith({
            email: email,
            code: code,
            password: password,
            passwordc: passwordConfirm
        })

        // Make sure the current step is set to 3 (done step)
        expect(wrapperVm.currentStep).toBe(4)
        expect(wrapper.text()).toContain('STEP_X - SUCCESS ! PASSWORD.RESET.SUCCESS')
    })

    test('third step handles api errors', async () => {
        // Set mocks for requestCode
        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode: vi.fn(),
            setPassword: vi.fn(),
            apiLoading: ref(false),
            apiError: ref({ title: 'Error', description: 'API second error', style: 'Danger' })
        })

        // Mount component, set test email, and emit submit event on the form
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 3
        await wrapperVm.$nextTick()
        expect(wrapper.text()).toContain('Error API second error')
    })

    test('resets the form when reset is called', async () => {
        // Set mocks for apiError
        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode: vi.fn(),
            setPassword: vi.fn(),
            apiLoading: ref(false),
            apiError: ref(null)
        })

        // Setup wrapper with some data, on step 2
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 2
        wrapperVm.email = email
        wrapperVm.code = code
        wrapperVm.password = password
        wrapperVm.passwordc = passwordConfirm
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
        expect(wrapperVm.password).toBe('')
        expect(wrapperVm.passwordc).toBe('')
        expect(wrapperVm.currentStep).toBe(1)
    })

    test('stays on request step when requestCode fails', async () => {
        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode: vi.fn().mockRejectedValue(new Error('failed')),
            setPassword: vi.fn(),
            apiLoading: ref(false),
            apiError: ref({ title: 'Error', description: 'Request failed', style: 'Danger' })
        })

        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.email = email
        const emailForm = wrapper.findComponent({ name: 'FormEmailVerificationRequest' })
        await emailForm.vm.$emit('submit')
        await wrapperVm.$nextTick()

        expect(wrapperVm.currentStep).toBe(1)
        expect(wrapper.text()).toContain('Request failed')
    })

    test('shows done-step login CTA and hides secondary card once completed', async () => {
        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm
        wrapperVm.currentStep = 4
        await wrapperVm.$nextTick()

        expect(wrapper.find('[data-test="gotoLogin"]').exists()).toBe(true)
        expect(wrapper.text()).toContain('PASSWORD.RESET.SUCCESS')
    })

    test('exposes and executes internal methods directly', async () => {
        const requestCode = vi.fn().mockResolvedValue('ok')
        const setPassword = vi.fn().mockResolvedValue('ok')
        const apiError = ref<any>({ title: 'Oops', description: 'Err', style: 'Danger' })

        vi.mocked(useForgotPasswordApi).mockReturnValue({
            requestCode,
            setPassword,
            apiLoading: ref(false),
            apiError
        })

        const wrapper = mount(PageForgotPassword, wrapperGlobals)
        const wrapperVm: any = wrapper.vm

        wrapperVm.email = email
        await wrapperVm.sendRequestCode()
        expect(requestCode).toHaveBeenCalledWith(email)
        expect(wrapperVm.currentStep).toBe(2)

        wrapperVm.code = code
        wrapperVm.password = password
        wrapperVm.passwordc = passwordConfirm
        wrapperVm.validateCode()
        expect(wrapperVm.currentStep).toBe(3)

        await wrapperVm.sendSetPassword()
        expect(setPassword).toHaveBeenCalledWith({
            email,
            code,
            password,
            passwordc: passwordConfirm
        })
        expect(wrapperVm.currentStep).toBe(4)

        wrapperVm.reset()
        expect(wrapperVm.currentStep).toBe(1)
        expect(apiError.value).toBeNull()
    })
})
