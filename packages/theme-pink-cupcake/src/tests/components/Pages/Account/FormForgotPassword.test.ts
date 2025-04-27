import { describe, test, expect, vi, afterEach } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import { forgotPassword } from '@userfrosting/sprinkle-account/composables'
import FormForgotPassword from '../../../../components/Pages/Account/FormForgotPassword.vue'
import UFAlert from '../../../../components/UFAlert.vue'

// Register the UFAlert component stub globally
config.global.stubs['UFAlert'] = UFAlert
config.global.stubs['FontAwesomeIcon'] = { template: '<span></span>' }

// Test email var
const email: String = 'john.doe@example.com'

// Mock composables
vi.mock('@userfrosting/sprinkle-account/composables', () => {
    return {
        forgotPassword: vi.fn()
    }
})

// Mocked response
const mockedResponse: AlertInterface = {
    description: 'Mocked message',
    style: Severity.Success,
    closeBtn: true
}

describe('FormForgotPassword.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    test('Renders correctly', () => {
        const wrapper = mount(FormForgotPassword)
        expect(wrapper.exists()).toBe(true)
    })

    test('Handles success', async () => {
        // Arrange
        vi.mocked(forgotPassword).mockResolvedValueOnce(mockedResponse)

        const wrapper = mount(FormForgotPassword)
        // @ts-ignore
        wrapper.vm.email = email
        await (wrapper.vm as any).sendForm()

        expect(forgotPassword).toHaveBeenCalledTimes(1)
        expect(forgotPassword).toHaveBeenCalledWith(email)
        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch('Mocked message')
    })

    test('Handles failure', async () => {
        // Arrange
        vi.mocked(forgotPassword).mockRejectedValueOnce(mockedResponse)

        const wrapper = mount(FormForgotPassword)
        // @ts-ignore
        wrapper.vm.email = email
        await (wrapper.vm as any).sendForm()

        // Spy on the authStore & UIkit notification method
        expect(forgotPassword).toHaveBeenCalledTimes(1)
        expect(forgotPassword).toHaveBeenCalledWith(email)
        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch('Mocked message')
    })

    test('Manages loading state correctly', async () => {
        // Arrange
        vi.mocked(forgotPassword).mockResolvedValueOnce(mockedResponse)

        const wrapper = mount(FormForgotPassword).vm as any
        expect(wrapper.loading).toBe(false)
        wrapper.email = email
        const sendPromise = wrapper.sendForm()
        expect(wrapper.loading).toBe(true)
        await sendPromise
        expect(wrapper.loading).toBe(false)
    })

    test('Handle form using the v-model', async () => {
        // Arrange
        vi.mocked(forgotPassword).mockResolvedValueOnce(mockedResponse)

        // Set forms values
        const wrapper = mount(FormForgotPassword)
        wrapper.find('[data-test="email"]').setValue(email)

        // Submit form
        await wrapper.find('form').trigger('submit')

        // Spy on the authStore & UIkit notification method
        expect(forgotPassword).toHaveBeenCalledTimes(1)
        expect(forgotPassword).toHaveBeenCalledWith(email)
    })
})
