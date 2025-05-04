import { expect, test, describe } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import SideBarItem from '../../../components/SideBar/SideBarItem.vue'
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
            component: SideBarItem
        }
    ]
})

describe('SideBarItem.vue', () => {
    test('Renders correctly with no props or slots', () => {
        const wrapper = mount(SideBarItem, {
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('a').text()).toBe('')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(false)
    })

    test('Renders with all props', () => {
        const wrapper = mount(SideBarItem, {
            props: {
                to: '/',
                label: 'Home',
                icon: 'icon-home',
                faIcon: 'fa-home'
            },
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('a').text()).toBe('Home')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="icon"]').attributes('data-uk-icon')).toBe('icon-home')
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="faIcon"]').classes('fa-home')).toBe(true)
    })

    test('Renders with slot', () => {
        const wrapper = mount(SideBarItem, {
            props: {
                to: { name: 'home' },
                label: 'Home',
                icon: 'icon-home',
                faIcon: 'fa-home'
            },
            slots: {
                default: 'This is the label slot'
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
    })

    test('External link', () => {
        const wrapper = mount(SideBarItem, {
            props: {
                to: 'http://userfrosting.com',
                label: 'Home',
                icon: 'icon-home',
                faIcon: 'fa-home'
            },
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('a').text()).toBe('Home')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="icon"]').attributes('data-uk-icon')).toBe('icon-home')
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="faIcon"]').classes('fa-home')).toBe(true)
    })

    test('External link without any icon / label', () => {
        const wrapper = mount(SideBarItem, {
            props: {
                to: 'http://userfrosting.com'
            },
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Assert each prop elements
        expect(wrapper.find('li').exists()).toBe(true)
        expect(wrapper.find('a').text()).toBe('')
        expect(wrapper.find('[data-test="icon"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="faIcon"]').exists()).toBe(false)
    })

    test('External link with slot', () => {
        const wrapper = mount(SideBarItem, {
            props: {
                to: 'http://userfrosting.com',
                label: 'Home',
                icon: 'icon-home',
                faIcon: 'fa-home'
            },
            slots: {
                default: 'This is the label slot'
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
    })

    test('Emits click event when clicked', async () => {
        const wrapper = mount(SideBarItem, {
            props: {
                to: '/',
                label: 'Home'
            },
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Simulate a click event
        await wrapper.find('a').trigger('click')

        // Assert that the click event was emitted
        expect(wrapper.emitted().click).toBeTruthy()
    })
})
