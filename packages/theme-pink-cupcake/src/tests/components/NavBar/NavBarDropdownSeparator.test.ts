import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import NavBarDropdownSeparator from '../../../components/NavBar/NavBarDropdownSeparator.vue'

describe('NavBarDropdownSeparator.vue', () => {
    test('Render correctly', () => {
        const wrapper = mount(NavBarDropdownSeparator)
        expect(wrapper.exists()).toBe(true)
    })
})
