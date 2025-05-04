import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import FormForgotPasswordSet from '../../../../components/Pages/Account/FormForgotPasswordSet.vue'

describe('FormForgotPasswordSet.vue', () => {
    test('renders the form correctly', () => {
        const wrapper = mount(FormForgotPasswordSet, {
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        expect(wrapper.find('form[data-test="newPasswordForm"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="password"]').exists()).toBe(true)
        expect(wrapper.find('input[data-test="passwordc"]').exists()).toBe(true)
        expect(wrapper.find('button[data-test="submitNewPassword"]').exists()).toBe(true)
    })

    test('disables the submit button when loading is true', async () => {
        const wrapper = mount(FormForgotPasswordSet, {
            props: { loading: true },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        const button = wrapper.find('button[data-test="submitNewPassword"]')
        expect(button.attributes('disabled')).toBeDefined()
    })

    test('enables the submit button when loading is false', async () => {
        const wrapper = mount(FormForgotPasswordSet, {
            props: { loading: false },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        const button = wrapper.find('button[data-test="submitNewPassword"]')
        expect(button.attributes('disabled')).toBeUndefined()
    })

    test('emits the submit event when the form is submitted', async () => {
        const wrapper = mount(FormForgotPasswordSet, {
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        await wrapper.find('form[data-test="newPasswordForm"]').trigger('submit.prevent')
        expect(wrapper.emitted('submit')).toBeTruthy()
    })

    test('binds password and passwordc inputs correctly', async () => {
        const wrapper = mount(FormForgotPasswordSet, {
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        const passwordInput = wrapper.find('input[data-test="password"]')
        const passwordcInput = wrapper.find('input[data-test="passwordc"]')

        await passwordInput.setValue('newpassword123')
        await passwordcInput.setValue('newpassword123')

        expect(wrapper.vm.password).toBe('newpassword123')
        expect(wrapper.vm.passwordc).toBe('newpassword123')
    })
})
