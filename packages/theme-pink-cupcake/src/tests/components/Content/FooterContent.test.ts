import { expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import FooterContent from '../../../components/Content/FooterContent.vue'

test('Basic test', () => {
    // Arrange
    const wrapper = mount(FooterContent, {
        slots: {
            default: 'This is the slot content'
        }
    })

    // Assert
    expect(wrapper.get('[data-test="slot"]').text()).toMatch('This is the slot content')
})
