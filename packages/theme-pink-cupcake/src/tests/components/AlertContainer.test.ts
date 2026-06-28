import { mount, config } from '@vue/test-utils'
import { describe, test, expect } from 'vitest'
import UFAlert from '../../components/UFAlert.vue'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import { library } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// Register font-awesome-icon component
library.add(fas)
config.global.stubs['FontAwesomeIcon'] = FontAwesomeIcon as any

describe('UFAlert', () => {
    test('renders correctly with empty alert', () => {
        // Arrange
        const myAlert: AlertInterface = {}
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.find('[data-test="title"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="description"]').exists()).toBe(false)
        expect(wrapper.find('[data-test="closeBtn"]').exists()).toBe(false)
        expect(wrapper.get('.uk-alert').classes('uk-alert-primary')).toBe(true)
        expect(wrapper.find('.uk-icon').exists()).toBe(false) // No title, no icon
    })

    test('renders correctly with complete alert', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert from Var',
            description: 'This is the description',
            style: Severity.Warning,
            closeBtn: true
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('[data-test="title"]').text()).toMatch('Alert from Var')
        expect(wrapper.get('[data-test="description"]').text()).toMatch('This is the description')
        expect(wrapper.find('[data-test="closeBtn"]').exists()).toBe(true)
        expect(wrapper.get('.uk-alert').classes('uk-alert-primary')).toBe(false)
        expect(wrapper.get('.uk-alert').classes('uk-alert-warning')).toBe(true)
        expect(wrapper.get('.uk-icon').classes('fa-circle-exclamation')).toBe(true)
    })

    // Test Slot
    test('renders correctly with slot', () => {
        // Arrange
        const myAlert: AlertInterface = {}
        const wrapper = mount(UFAlert, {
            slots: {
                default: 'This is a slot'
            },
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('[data-test="description"]').text()).toMatch('This is a slot')
    })

    // Test Slot and props - Slot overwrites props
    test('renders correctly with slot', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert from Var',
            description: 'This is the description',
            style: Severity.Warning,
            closeBtn: true
        }
        const wrapper = mount(UFAlert, {
            slots: {
                default: 'This is a slot'
            },
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('[data-test="description"]').text()).toMatch('This is a slot')
    })

    // Test hide Icon
    test('renders correctly with hidden icon', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert with hidden icon',
            description: 'This alert has a hidden icon',
            style: Severity.Success,
            hideIcon: true
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('[data-test="title"]').text()).toMatch('Alert with hidden icon')
        expect(wrapper.get('[data-test="description"]').text()).toMatch(
            'This alert has a hidden icon'
        )
        expect(wrapper.find('[data-test="closeBtn"]').exists()).toBe(false)
        expect(wrapper.find('.uk-alert-icon').exists()).toBe(false)
        expect(wrapper.get('.uk-alert').classes('uk-alert-success')).toBe(true)
        expect(wrapper.find('.uk-icon').exists()).toBe(false) // No title, no icon
    })

    // Test style Success
    test('renders correctly with style Success', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert',
            style: Severity.Success
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('.uk-alert').classes('uk-alert-success')).toBe(true)
        expect(wrapper.get('.uk-icon').classes('fa-circle-check')).toBe(true)
    })

    // Test style Warning
    test('renders correctly with style Warning', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert',
            style: Severity.Warning
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('.uk-alert').classes('uk-alert-warning')).toBe(true)
        expect(wrapper.get('.uk-icon').classes('fa-circle-exclamation')).toBe(true)
    })

    // Test style Danger
    test('renders correctly with style Danger', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert',
            style: Severity.Danger
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('.uk-alert').classes('uk-alert-danger')).toBe(true)
        expect(wrapper.get('.uk-icon').classes('fa-triangle-exclamation')).toBe(true)
    })

    // Test style Primary
    test('renders correctly with style Primary', () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert',
            style: Severity.Primary
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert
        expect(wrapper.get('.uk-alert').classes('uk-alert-primary')).toBe(true)
        expect(wrapper.get('.uk-icon').classes('fa-circle-info')).toBe(true)
    })

    // Test click close button
    test('click close button', async () => {
        // Arrange
        const myAlert: AlertInterface = {
            title: 'Alert',
            closeBtn: true
        }
        const wrapper = mount(UFAlert, {
            props: {
                alert: myAlert
            }
        })

        // Assert initial state
        // TODO : Fix closing alert deleting the DOM
        // expect(wrapper.find('.uk-alert').exists()).toBe(true)
        // expect(wrapper.find('[data-test="title"]').exists()).toBe(true)

        // Act
        await wrapper.get('[data-test="closeBtn"]').trigger('click')

        // Assert new state
        expect(wrapper.emitted('close')).toBeTruthy()
        // TODO : Fix closing alert deleting the DOM
        // expect(wrapper.find('[data-test="title"]').exists()).toBe(false)
        // expect(wrapper.find('.uk-alert').exists()).toBe(false)
    })
})
