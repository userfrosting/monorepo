import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import SideBarUserCard from '../../../components/SideBar/SideBarUserCard.vue'

describe('SideBarUserCard.vue', () => {
    test('renders avatar, username and meta when provided', () => {
        const wrapper = mount(SideBarUserCard, {
            props: {
                avatar: '/avatar.png',
                username: 'jane',
                meta: 'Admin'
            }
        })

        expect(wrapper.get('img').attributes('src')).toBe('/avatar.png')
        expect(wrapper.get('[data-test="username"]').text()).toBe('jane')
        expect(wrapper.get('[data-test="meta"]').text()).toContain('Admin')
    })

    test('renders nothing optional when props are omitted', () => {
        const wrapper = mount(SideBarUserCard)

        expect(wrapper.find('img').exists()).toBe(false)
        expect(wrapper.find('[data-test="username"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="meta"]').exists()).toBe(false)
    })
})
