import { expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import CardBox from '../../../components/Content/CardBox.vue'

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
})
