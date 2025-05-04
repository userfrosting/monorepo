import { expect, test, describe } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import SideBarDropdown from '../../../components/SideBar/SideBarDropdown.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// Register font-awesome-icon component
library.add(fas)
config.global.stubs['FontAwesomeIcon'] = FontAwesomeIcon

// Mock router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: SideBarDropdown
        }
    ]
})

describe('SideBarDropdown.vue', () => {
    test('Renders correctly with no props or slots', () => {
        const wrapper = mount(SideBarDropdown, {
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('li').classes('uk-open')).toBe(false)
        expect(wrapper.find('a').text()).toBe('')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="caret"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="slot"]').text()).toBe('')
    })

    test('Renders with all props', () => {
        const wrapper = mount(SideBarDropdown, {
            props: {
                to: '/',
                label: 'Home',
                icon: 'icon-home',
                faIcon: 'fa-home',
                hideCaret: true
            },
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('li').classes('uk-open')).toBe(false)
        expect(wrapper.find('a').text()).toBe('Home')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="icon"]').attributes('data-uk-icon')).toBe('icon-home')
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="faIcon"]').classes('fa-home')).toBe(true)
        expect(wrapper.find('[data-test="caret"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="slot"]').text()).toBe('')
    })

    test('Renders with all slots', () => {
        const wrapper = mount(SideBarDropdown, {
            props: {
                to: { name: 'home' },
                label: 'Home',
                icon: 'icon-home',
                faIcon: 'fa-home',
                hideCaret: true
            },
            slots: {
                label: 'This is the label slot',
                default: 'This is the slot content'
            },
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('a').text()).toBe('This is the label slot')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="icon"]').attributes('data-uk-icon')).toBe('icon-home')
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="faIcon"]').classes('fa-home')).toBe(true)
        expect(wrapper.find('[data-test="caret"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="slot"]').text()).toBe('This is the slot content')
    })
})
