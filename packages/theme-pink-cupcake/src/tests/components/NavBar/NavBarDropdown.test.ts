import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import MainContent from '../../../components/NavBar/NavBarDropdown.vue'

describe('NavBarDropdown.vue', () => {
    test('Label as slot', () => {
        // Arrange
        const wrapper = mount(MainContent, {
            slots: {
                label: 'This is the Label as Slot',
                default: 'This is the slot content'
            }
        })

        // Assert
        expect(wrapper.get('[data-test="label"]').text()).toMatch('This is the Label as Slot')
        expect(wrapper.get('[data-test="slot"]').text()).toMatch('This is the slot content')
    })

    test('Label as prop', () => {
        // Arrange
        const wrapper = mount(MainContent, {
            props: {
                label: 'This is the Label as prop'
            },
            slots: {
                default: 'This is the slot content'
            }
        })

        // Assert
        expect(wrapper.get('[data-test="label"]').text()).toMatch('This is the Label as prop')
        expect(wrapper.get('[data-test="slot"]').text()).toMatch('This is the slot content')
    })

    test('Label slot overwrite prop', () => {
        // Arrange
        const wrapper = mount(MainContent, {
            props: {
                label: 'This is the Label as prop'
            },
            slots: {
                label: 'This is the Label as Slot'
            }
        })

        // Assert
        expect(wrapper.get('[data-test="label"]').text()).toMatch('This is the Label as Slot')
        expect(wrapper.get('[data-test="slot"]').text()).toMatch('')
    })
})
