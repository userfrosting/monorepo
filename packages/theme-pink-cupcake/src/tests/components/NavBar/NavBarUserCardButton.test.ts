import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import NavBarUserCardButton from '../../../components/NavBar/NavBarUserCardButton.vue'

// Mock router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: NavBarUserCardButton
        }
    ]
})

describe('NavBarUserCardButton.vue', () => {
    test('Renders correctly with no props or slots', () => {
        const wrapper = mount(NavBarUserCardButton, {
            global: {
                plugins: [router]
            }
        })
        expect(wrapper.exists()).toBe(true)
    })

    test('External link, using both props', () => {
        // Arrange
        const wrapper = mount(NavBarUserCardButton, {
            props: {
                to: 'http://userfrosting.com',
                label: 'UserFrosting'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.text()).toMatch('UserFrosting')
    })

    test('External link, using slot', () => {
        // Arrange
        const wrapper = mount(NavBarUserCardButton, {
            props: {
                to: 'http://userfrosting.com',
                label: 'This is the Label as prop'
            },
            slots: {
                default: 'This is the slot content'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.text()).toMatch('This is the slot content')
    })

    test('Internal, using both props', () => {
        // Arrange
        const wrapper = mount(NavBarUserCardButton, {
            props: {
                to: '/',
                label: 'UserFrosting'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.text()).toMatch('UserFrosting')
    })

    test('Internal, using both slot, object for to', () => {
        // Arrange
        const wrapper = mount(NavBarUserCardButton, {
            props: {
                to: { name: 'home' },
                label: 'This is the Label as prop'
            },
            slots: {
                default: 'This is the slot content'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.text()).toMatch('This is the slot content')
    })
})
