import { expect, test, describe } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import NavBar from '../../../components/NavBar/NavBar.vue'
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
            component: NavBar
        }
    ]
})

describe('NavBar.vue', () => {
    test('Renders correctly with no props or slots', () => {
        const wrapper = mount(NavBar, {
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.get('[data-test="navbar-left"]').text()).toMatch('')
        expect(wrapper.get('[data-test="navbar-right"]').text()).toMatch('')
    })

    test('Title props provided', () => {
        // Arrange
        const wrapper = mount(NavBar, {
            props: {
                title: 'This is the Label as prop',
                to: '/'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.get('[data-test="navbar-left"]').text()).toMatch('This is the Label as prop')
    })

    test('Using object for to', () => {
        // Arrange
        const wrapper = mount(NavBar, {
            props: {
                title: 'This is the Label as prop',
                to: { name: 'home' }
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.get('[data-test="navbar-left"]').text()).toMatch('This is the Label as prop')
    })

    test('Title slot provided, and it overwrites the props', () => {
        // Arrange
        const wrapper = mount(NavBar, {
            props: {
                label: 'This is the Label as prop'
            },
            slots: {
                title: 'This is the slot content'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.get('[data-test="navbar-left"]').text()).toMatch('This is the slot content')
    })

    test('Right slot provided', () => {
        // Arrange
        const wrapper = mount(NavBar, {
            slots: {
                default: '<li>This is the right slot content</li>'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.get('[data-test="navbar-right"]').text()).toMatch(
            'This is the right slot content'
        )
    })
})
