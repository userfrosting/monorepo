import { expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import MainContent from '../../../components/Content/MainContent.vue'

test('Basic test', () => {
    // Arrange
    const wrapper = mount(MainContent, {
        slots: {
            default: 'This is the slot content'
        }
    })

    // Assert
    expect(wrapper.get('[data-test="slot"]').text()).toMatch('This is the slot content')
})
