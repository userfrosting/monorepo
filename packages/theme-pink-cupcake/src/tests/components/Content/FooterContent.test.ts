import { expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import UFFooter from '../../../components/Content/UFFooter.vue'

test('Basic test', () => {
    // Arrange
    const wrapper = mount(UFFooter, {
        slots: {
            default: 'This is the slot content'
        }
    })

    // Assert
    expect(wrapper.get('[data-test="slot"]').text()).toMatch('This is the slot content')
})
