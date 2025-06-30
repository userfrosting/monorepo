import { describe, test, afterEach, expect, vi } from 'vitest'
import { ref } from 'vue'
import { mount, config } from '@vue/test-utils'
import { useLoginApi } from '@userfrosting/sprinkle-account/composables'
import type { LoginRequest } from '@userfrosting/sprinkle-account/interfaces'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import FormLogin from '../../../../components/Pages/Account/FormLogin.vue'
import UFAlert from '../../../../components/UFAlert.vue'

// Register the UFAlert component stub globally
config.global.stubs['UFAlert'] = UFAlert
config.global.stubs['FontAwesomeIcon'] = { template: '<span></span>' }

// Login form data
const form: LoginRequest = {
    user_name: 'john.doe',
    password: 'password',
    rememberme: true
}

// Mock the auth store
vi.mock('@userfrosting/sprinkle-account/composables')
const mockUseLoginApi = {
    submitLogin: vi.fn().mockResolvedValue({}),
    defaultFormData: vi.fn().mockReturnValue(form),
    apiError: ref<AlertInterface | null>(null),
    apiLoading: ref(false)
}

describe('FormLogin.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    test('handles successful login', async () => {
        vi.mocked(useLoginApi).mockReturnValue(mockUseLoginApi as any)

        const wrapper = mount(FormLogin)
        expect(wrapper.exists()).toBe(true)
        await (wrapper.vm as any).sendLogin(form)

        expect(useLoginApi).toHaveBeenCalled()
        expect(mockUseLoginApi.submitLogin).toHaveBeenCalledTimes(1)
        expect(wrapper.find('[data-test="error"]').exists()).toBe(false)
        expect(wrapper.find('button[data-test="submit"]').attributes('disabled')).not.toBeDefined()
    })

    test('handles apiError', async () => {
        const error: AlertInterface = { title: 'Invalid credentials' }
        mockUseLoginApi.apiError.value = error
        vi.mocked(useLoginApi).mockReturnValue(mockUseLoginApi as any)

        const wrapper = mount(FormLogin)

        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch('Invalid credentials')
    })

    test('handles apiLoading', async () => {
        mockUseLoginApi.apiLoading.value = true
        vi.mocked(useLoginApi).mockReturnValue(mockUseLoginApi as any)

        const wrapper = mount(FormLogin)

        expect(wrapper.find('button[data-test="submit"]').attributes('disabled')).toBeDefined()
    })

    test('Handle login using the v-model', async () => {
        vi.mocked(useLoginApi).mockReturnValue(mockUseLoginApi as any)

        const wrapper = mount(FormLogin)
        wrapper.find('[data-test="username"]').setValue('doe.john')
        wrapper.find('[data-test="password"]').setValue('drowssap')
        wrapper.find('[data-test="rememberme"]').setValue(false)
        await wrapper.find('form').trigger('submit')

        expect(useLoginApi).toHaveBeenCalled()
        expect(mockUseLoginApi.submitLogin).toHaveBeenCalledTimes(1)
        expect(mockUseLoginApi.submitLogin).toHaveBeenCalledWith({
            user_name: 'doe.john',
            password: 'drowssap',
            rememberme: false
        })
    })
})
