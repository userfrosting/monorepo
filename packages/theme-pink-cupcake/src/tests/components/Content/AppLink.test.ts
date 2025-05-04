import { expect, test, describe } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import AppLink from '../../../components/Content/AppLink.vue'

describe('AppLink.vue', () => {
    test('External, with slot overwrite', () => {
        // Arrange
        const wrapper = mount(AppLink, {
            props: {
                label: 'Hello world',
                to: 'http://google.com'
            },
            slots: {
                default: 'This is the slot content'
            }
        })

        // Assert
        expect(wrapper.html()).toMatch(
            '<a href="http://google.com" target="_blank">This is the slot content</a>'
        )
    })

    test('External, with label', () => {
        // Arrange
        const wrapper = mount(AppLink, {
            props: {
                label: 'Hello world',
                to: 'http://google.com'
            }
        })

        // Assert
        expect(wrapper.html()).toMatch(
            '<a href="http://google.com" target="_blank">Hello world</a>'
        )
    })

    // Mock router
    const router = createRouter({
        history: createWebHistory(),
        routes: [
            {
                path: '/',
                name: 'home',
                component: AppLink
            }
        ]
    })

    test('Internal, with label', () => {
        // Arrange
        const wrapper = mount(AppLink, {
            props: {
                label: 'Hello world',
                to: '/'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.html()).toMatch('<a href="/" class="">Hello world</a>')
    })

    test('Internal, with slot', () => {
        // Arrange
        const wrapper = mount(AppLink, {
            props: {
                label: 'Hello world',
                to: { name: 'home' }
            },
            slots: {
                default: 'This is the slot content'
            },
            global: {
                plugins: [router]
            }
        })

        // Assert
        expect(wrapper.html()).toMatch('<a href="/" class="">This is the slot content</a>')
    })
})
