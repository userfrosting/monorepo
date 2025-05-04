import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import SideBar from '../../../components/SideBar/SideBar.vue'

describe('SideBar.vue', () => {
    test('Render correctly', () => {
        const wrapper = mount(SideBar)
        expect(wrapper.exists()).toBe(true)
    })
})
