import { ref } from 'vue'
import { describe, test, expect, vi, afterEach } from 'vitest'
import { mount, config } from '@vue/test-utils'
import UIkit from 'uikit'
import FormEmailVerificationRequest from '../../../../components/Pages/Account/FormEmailVerificationRequest.vue'
import { useEmailVerificationApi } from '@userfrosting/sprinkle-account/composables'
import { useRouter } from 'vue-router'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import UFAlert from '../../../../components/UFAlert.vue'

// Register the global component stubs
config.global.stubs['UFAlert'] = UFAlert
config.global.stubs['FontAwesomeIcon'] = { template: '<span></span>' }

// Mock dependencies
vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useEmailVerificationApi: vi.fn(() => ({
        apiLoading: ref(false),
        apiError: ref(null),
        submitVerificationCode: vi.fn(),
        resendVerification: vi.fn()
    }))
}))

vi.mock('vue-router', () => ({
    useRouter: vi.fn(() => ({
        push: vi.fn()
    }))
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: vi.fn(() => ({
        get: vi.fn(() => true)
    }))
}))

// Test email & code vars
const email: String = 'john.doe@example.com'
const code: String = '123456'

// UiKit notification data
const uikitNotificationRequestSent = {
    message: 'Verification email sent',
    status: 'success',
    pos: 'top-right',
    timeout: 4000
}
const uikitNotificationValidated = {
    message: 'Code verified',
    status: 'success',
    pos: 'top-right',
    timeout: 4000
}

describe('FormEmailVerificationRequest.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    test('renders email form initially', () => {
        const wrapper = mount(FormEmailVerificationRequest)
        expect(wrapper.find('form').exists()).toBe(true)
        expect(wrapper.find('input[data-test="email"]').exists()).toBe(true)
        expect(wrapper.find('button[data-test="submitRequest"]').text()).toBe(
            'ACCOUNT.VERIFICATION.SEND'
        )
    })

    test('shows verification form after sending email', async () => {
        // Set mocks
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            resendVerification: vi.fn().mockResolvedValue('Verification email sent'),
            submitVerificationCode: vi.fn(),
            apiLoading: ref(false),
            apiError: ref(null)
        })
        vi.spyOn(UIkit, 'notification')

        // Mount the component
        const wrapper = mount(FormEmailVerificationRequest)

        // Test initial state
        // @ts-ignore
        expect(wrapper.vm.displayVerification).toBe(false)

        // Set form and submit
        await wrapper.find('input[data-test="email"]').setValue(email)
        await wrapper.find('form[data-test="requestForm"]').trigger('submit.prevent')

        // Expect the API call to be made and the result to be handled
        // @ts-ignore
        expect(wrapper.vm.displayVerification).toBe(true)
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.resendVerification
        ).toHaveBeenCalled()
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.resendVerification
        ).toHaveBeenCalledWith(email)
        expect(UIkit.notification).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledWith(uikitNotificationRequestSent)
        expect(wrapper.find('h3').text()).toBe('ACCOUNT.VERIFICATION.CODE.ENTER')
        expect(wrapper.find('[data-test="faCode"]').exists()).toBe(true)
    })

    test('submits verification code and redirects on success', async () => {
        // Set mocks
        vi.mocked(useEmailVerificationApi).mockReturnValue({
            resendVerification: vi.fn(),
            submitVerificationCode: vi.fn().mockResolvedValue({ message: 'Code verified' }),
            apiLoading: ref(false),
            apiError: ref(null)
        })
        vi.spyOn(UIkit, 'notification')

        // Mount the component
        const wrapper = mount(FormEmailVerificationRequest)

        // Set email before next part
        await wrapper.find('input[data-test="email"]').setValue(email)

        // Force displayVerification to true, simulating the request already been sent
        // @ts-ignore
        wrapper.vm.displayVerification = true
        await wrapper.vm.$nextTick()

        // Test initial state
        // @ts-ignore
        expect(wrapper.vm.displayVerification).toBe(true)
        expect(wrapper.find('[data-test="faCode"]').exists()).toBe(true)

        // Set form and submit
        await wrapper.find('input[data-test="2FACode"]').setValue(code)
        await wrapper.find('form[data-test="verificationForm"]').trigger('submit.prevent')

        // Expect the API call to be made and the result to be handled
        // @ts-ignore
        expect(wrapper.vm.displayVerification).toBe(false)
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.submitVerificationCode
        ).toHaveBeenCalled()
        expect(
            vi.mocked(useEmailVerificationApi).mock.results[0].value.submitVerificationCode
        ).toHaveBeenCalledWith(email, code)
        expect(UIkit.notification).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledWith(uikitNotificationValidated)
        // TODO : expect(router.push).toHaveBeenCalledWith({ name: 'account.login' })
    })

    test('resets the form when "Try Again" is clicked', async () => {
        // Mount the component
        const wrapper = mount(FormEmailVerificationRequest)

        // Set email before next part
        await wrapper.find('input[data-test="email"]').setValue(email)

        // Force displayVerification to true, simulating the request already been sent
        // @ts-ignore
        wrapper.vm.displayVerification = true
        await wrapper.vm.$nextTick()

        // Set code form
        await wrapper.find('input[data-test="2FACode"]').setValue(code)

        // Assert initial state
        // @ts-ignore
        expect(wrapper.vm.displayVerification).toBe(true)
        // @ts-ignore
        expect(wrapper.vm.email).toBe(email)
        // @ts-ignore
        expect(wrapper.vm.code).toBe(code)
        expect(wrapper.find('[data-test="faCode"]').exists()).toBe(true)

        // Trigger the "Try Again" button
        expect(wrapper.find('button[data-test="tryAgain"]').exists()).toBe(true)
        await wrapper.find('button[data-test="tryAgain"]').trigger('click')

        // Assert new state
        // @ts-ignore
        expect(wrapper.vm.email).toBe('')
        // @ts-ignore
        expect(wrapper.vm.code).toBe('')
        // @ts-ignore
        expect(wrapper.vm.displayVerification).toBe(false)
    })

    // TODO
    // test('redirects if email verification is not required', () => {
    //     const configStore = useConfigStore()
    //     configStore.get.mockReturnValue(false)
    //     const router = useRouter()

    //     mount(FormEmailVerificationRequest)
    //     expect(router.push).toHaveBeenCalledWith({ name: 'account.login' })
    // })

    // TODO : Test error handling
    // TODO : Test loading state
})
