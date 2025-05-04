import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import NavBarUserCard from '../../../components/NavBar/NavBarUserCard.vue'

describe('NavBarUserCard.vue', () => {
    test('Render correctly, no props or slots', () => {
        const wrapper = mount(NavBarUserCard)
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('img').exists()).toBe(false)
        expect(wrapper.find('[data-test="username"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="meta"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="slot"]').text()).toBe('')
    })

    test('Use props and slots', () => {
        const wrapper = mount(NavBarUserCard, {
            props: {
                username: 'admin',
                meta: 'Administrator',
                avatar: 'https://via.placeholder.com/150'
            },
            slots: {
                default: 'This is the slot content'
            }
        })
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('img').exists()).toBe(true)
        expect(wrapper.find('img').attributes('src')).toBe('https://via.placeholder.com/150')
        expect(wrapper.find('[data-test="username"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="username"]').text()).toBe('admin')
        expect(wrapper.find('[data-test="meta"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="meta"]').text()).toContain('Administrator')
        expect(wrapper.find('[data-test="slot"]').text()).toContain('This is the slot content')
    })
})
