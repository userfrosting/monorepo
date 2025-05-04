import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import FormEmailVerificationValidation from '../../../../components/Pages/Account/FormEmailVerificationValidation.vue'
import FaCode from '../../../../components/Content/2FaCode.vue'

describe('FormEmailVerificationValidation.vue', () => {
    test('renders the form and buttons correctly', () => {
        const wrapper = mount(FormEmailVerificationValidation, {
            props: { loading: false },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })

        expect(wrapper.find('form[data-test="verificationForm"]').exists()).toBe(true)
        expect(wrapper.find('button[data-test="submitVerification"]').exists()).toBe(true)
        expect(wrapper.find('button[data-test="tryAgain"]').exists()).toBe(true)
        expect(wrapper.findComponent(FaCode).exists()).toBe(true)
    })

    test('disables buttons when loading is true', () => {
        const wrapper = mount(FormEmailVerificationValidation, {
            props: { loading: true },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })

        expect(
            wrapper.find('button[data-test="submitVerification"]').attributes('disabled')
        ).toBeDefined()
        expect(wrapper.find('button[data-test="tryAgain"]').attributes('disabled')).toBeDefined()
    })

    test('emits submit event when the form is submitted', async () => {
        const wrapper = mount(FormEmailVerificationValidation, {
            props: { loading: false },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })

        await wrapper.find('form[data-test="verificationForm"]').trigger('submit.prevent')
        expect(wrapper.emitted('submit')).toBeTruthy()
    })

    test('emits reset event when the try again button is clicked', async () => {
        const wrapper = mount(FormEmailVerificationValidation, {
            props: { loading: false },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })

        await wrapper.find('button[data-test="tryAgain"]').trigger('click')
        expect(wrapper.emitted('reset')).toBeTruthy()
    })

    test('binds the code model to the FaCode component', async () => {
        const wrapper = mount(FormEmailVerificationValidation, {
            props: { loading: false },
            global: {
                stubs: ['FontAwesomeIcon']
            }
        })

        const faCode = wrapper.findComponent(FaCode)
        await faCode.setValue('123456')
        expect((wrapper.vm as any).code).toBe('123456')
    })
})
