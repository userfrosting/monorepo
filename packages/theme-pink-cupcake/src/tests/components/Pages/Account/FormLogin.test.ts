import { describe, test, beforeEach, expect, vi } from 'vitest'
import { ref } from 'vue'
import { mount, flushPromises } from '@vue/test-utils'
import FormLogin from '../../../../components/Pages/Account/FormLogin.vue'
import UFAlert from '../../../../components/UFAlert.vue'

// Mock composables and dependencies
const mockedValidateResult = vi.fn().mockReturnValue({ valid: true })
const mockedSubmitLogin = vi.fn().mockResolvedValue(undefined)
vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useLoginApi: () => ({
        submitLogin: mockedSubmitLogin,
        formData: ref({
            user_name: '',
            password: '',
            rememberme: false
        }),
        apiLoading: ref(false),
        apiError: ref(null),
        r$: {
            $validate: mockedValidateResult,
            $errors: {
                user_name: [],
                password: [],
                rememberme: []
            },
            user_name: { $error: false },
            password: { $error: false },
            rememberme: { $error: false }
        }
    })
}))

describe('FormLogin.vue', () => {
    let wrapper: any

    beforeEach(() => {
        wrapper = mount(FormLogin, {
            global: {
                stubs: {
                    UFAlert: UFAlert,
                    UFFormValidationError: { template: '<span></span>' },
                    FontAwesomeIcon: { template: '<span></span>' }
                }
            }
        })
        vi.clearAllMocks()
    })

    test('renders correctly', () => {
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('input[data-test="username"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="password"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="rememberme"]').exists()).toBe(true)
    })

    test('handles successful login', async () => {
        await wrapper.find('input[data-test="username"]').setValue('johndoe')
        await wrapper.find('input[data-test="password"]').setValue('password123')
        await wrapper.find('input[data-test="rememberme"]').setValue(true)
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        expect(wrapper.vm.formData).toEqual({
            user_name: 'johndoe',
            password: 'password123',
            rememberme: true
        })
        expect(mockedSubmitLogin).toHaveBeenCalledWith({
            user_name: 'johndoe',
            password: 'password123',
            rememberme: true
        })
        expect(wrapper.find('[data-test="error"]').exists()).toBe(false)
        expect(wrapper.find('button[data-test="submit"]').attributes('disabled')).not.toBeDefined()
    })

    test('disables submit button when loading', async () => {
        wrapper.vm.apiLoading = true
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[data-test="submit"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="submit"]').text()).toBe('LOGIN')
        expect(wrapper.find('[data-test="submit"]').attributes().disabled).toBeDefined()
    })

    test('handles apiError', async () => {
        wrapper.vm.apiError = {
            title: 'Invalid credentials',
            description: 'User not found or password is invalid.',
            style: 'Danger',
            closeBtn: true
        }
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch('Invalid credentials')
    })

    test('validates error', async () => {
        mockedValidateResult.mockReturnValueOnce({ valid: false })

        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        expect(mockedSubmitLogin).not.toHaveBeenCalled()
    })

    test('catch error on submitLogin error', async () => {
        mockedSubmitLogin.mockRejectedValueOnce(new Error('login failed'))
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()

        expect(wrapper.find('form').exists()).toBe(true)
        expect(mockedSubmitLogin).toHaveBeenCalled()
    })
})
