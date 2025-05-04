import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import SideBarLabel from '../../../components/SideBar/SideBarLabel.vue'

describe('SideBarLabel.vue', () => {
    test('Render correctly', () => {
        const wrapper = mount(SideBarLabel, {
            props: {
                label: 'The Label'
            }
        })
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('li').text()).toBe('The Label')
    })
})
