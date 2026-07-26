import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import InfoBox from '../../../components/Content/InfoBox.vue'

describe('InfoBox.vue', () => {
    const globalStubs = {
        AppLink: { template: '<a><slot /></a>' },
        'font-awesome-icon': { template: '<span />' }
    }

    test('renders icon, value, and label with uikit icon', () => {
        const wrapper = mount(InfoBox, {
            props: {
                to: '/dashboard',
                label: 'Users',
                value: 42,
                icon: 'home'
            },
            global: {
                stubs: globalStubs
            }
        })

        expect(wrapper.text()).toContain('42')
        expect(wrapper.text()).toContain('Users')
        expect(wrapper.find('[data-uk-icon]').attributes('data-uk-icon')).toContain('icon: home')
    })

    test('renders font-awesome icon when faIcon is provided', () => {
        const wrapper = mount(InfoBox, {
            props: {
                to: { name: 'admin.dashboard' },
                faIcon: 'user'
            },
            global: {
                stubs: {
                    ...globalStubs,
                    'font-awesome-icon': { template: '<span data-test="fa-icon" />' }
                }
            }
        })

        expect(wrapper.find('[data-test="fa-icon"]').exists()).toBe(true)
        expect(wrapper.find('h4').exists()).toBe(false)
        expect(wrapper.find('span.uk-text-meta').exists()).toBe(false)
    })

    test('renders no icon', () => {
        const wrapper = mount(InfoBox, {
            props: {
                to: '/dashboard',
                label: 'Users',
                value: 42
            },
            global: {
                stubs: globalStubs
            }
        })

        expect(wrapper.text()).toContain('42')
        expect(wrapper.text()).toContain('Users')
        expect(wrapper.find('[data-uk-icon]').exists()).toBe(false)
        expect(wrapper.find('[font-awesome-icon]').exists()).toBe(false)
    })
})
