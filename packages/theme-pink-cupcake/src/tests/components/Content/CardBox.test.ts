import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import CardBox from '../../../components/Content/CardBox.vue'

describe('CardBox.vue', () => {
    test('Basic test', () => {
        // Arrange
        const wrapper = mount(CardBox, {
            props: {
                title: 'Hello world'
            },
            slots: {
                default: 'This is the slot content'
            }
        })

        // Assert
        expect(wrapper.get('[data-test="title"]').text()).toMatch('Hello world')
        expect(wrapper.get('[data-test="slot"]').text()).toMatch('This is the slot content')
        expect(wrapper.find('[data-test="footer"]').exists()).toBe(false)
    })

    test('Test header fallback with empty header slot and no title', () => {
        // Arrange
        const wrapper = mount(CardBox, {
            slots: {
                // Provide an empty slot function so `$slots.header` is truthy,
                // then let `<slot>` fallback render the translated empty title.
                header: () => []
            }
        })

        // Assert
        expect(wrapper.find('[data-test="title"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="title"]').text()).toMatch('')
    })

    test('Test with no prop and slot', () => {
        // Arrange
        const wrapper = mount(CardBox)

        // Assert
        expect(wrapper.find('[data-test="title"]').exists()).toBe(false)
        expect(wrapper.get('[data-test="slot"]').text()).toMatch('')
        expect(wrapper.find('[data-test="footer"]').exists()).toBe(false)
    })

    test('Test with footer slot', () => {
        // Arrange
        const wrapper = mount(CardBox, {
            slots: {
                footer: 'This is the slot content'
            }
        })

        // Assert
        expect(wrapper.find('[data-test="footer"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="footer"]').text()).toMatch('This is the slot content')
    })
})
