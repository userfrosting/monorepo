import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import FaCode from '../../../components/Content/2FaCode.vue'

describe('2FaCode.vue', () => {
    test('With default props', () => {
        // Arrange
        const wrapper = mount(FaCode, {
            global: {
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        // Assert
        expect(wrapper.find('[data-test="2FACode"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="2FACode"]').attributes('size')).toBe('6')
    })

    test('With default length prop', () => {
        // Arrange
        const wrapper = mount(FaCode, {
            props: { length: 4 },
            global: {
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        // Assert
        expect(wrapper.find('[data-test="2FACode"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="2FACode"]').attributes('size')).toBe('4')
    })

    test('With model value', async () => {
        // Arrange
        const wrapper = mount(FaCode, {
            props: { modelValue: '123456' },
            global: {
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        // Assert
        const textInput = wrapper.find('[data-test="2FACode"]')

        expect(textInput.exists()).toBe(true)
        expect(wrapper.props('modelValue')).toBe('123456')
        expect((textInput.element as HTMLInputElement).value).toBe('123456')
        await textInput.setValue('654321')
        expect((textInput.element as HTMLInputElement).value).toBe('654321')
    })
})
