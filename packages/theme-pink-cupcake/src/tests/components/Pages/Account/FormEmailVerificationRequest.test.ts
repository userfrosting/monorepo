import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import FormEmailVerificationRequest from '../../../../components/Pages/Account/FormEmailVerificationRequest.vue'

describe('FormEmailVerificationRequest.vue', () => {
    test('renders the form correctly', () => {
        const wrapper = mount(FormEmailVerificationRequest, {
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        expect(wrapper.find('form').exists()).toBe(true)
        expect(wrapper.find('input[type="email"]').exists()).toBe(true)
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
    })

    test('binds the email input correctly', async () => {
        const wrapper = mount(FormEmailVerificationRequest, {
            props: {
                modelValue: ''
            },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        const emailInput = wrapper.find('input[type="email"]')
        await emailInput.setValue('test@example.com')
        expect((emailInput.element as HTMLInputElement).value).toBe('test@example.com')
    })

    test('disables the submit button when loading is true', () => {
        const wrapper = mount(FormEmailVerificationRequest, {
            props: {
                loading: true
            },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        const submitButton = wrapper.find('button[type="submit"]')
        expect(submitButton.attributes('disabled')).toBeDefined()
    })

    test('emits the submit event when the form is submitted', async () => {
        const wrapper = mount(FormEmailVerificationRequest, {
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })
        await wrapper.find('form').trigger('submit.prevent')
        expect(wrapper.emitted('submit')).toBeTruthy()
    })
})
