import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import UFModalAlert from '../../../components/Modals/UFModalAlert.vue'

describe('UFModalAlert.vue', () => {
    test('renders title, prompt and default button', () => {
        const wrapper = mount(UFModalAlert, {
            props: {
                title: 'Warning',
                prompt: 'Pay attention',
                btnLabel: 'Okay'
            },
            global: {
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    }
                }
            }
        })

        expect(wrapper.get('[data-test="header"]').text()).toContain('Warning')
        expect(wrapper.get('[data-test="body"]').text()).toContain('Pay attention')
        expect(wrapper.get('[data-test="footer"]').text()).toContain('Okay')
    })

    test('renders custom slots when provided', () => {
        const wrapper = mount(UFModalAlert, {
            slots: {
                header: 'Custom Header',
                default: 'Custom Prompt',
                footer: 'Custom Footer'
            },
            global: {
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    }
                }
            }
        })

        expect(wrapper.get('[data-test="header"]').text()).toContain('Custom Header')
        expect(wrapper.get('[data-test="body"]').text()).toContain('Custom Prompt')
        expect(wrapper.get('[data-test="footer"]').text()).toContain('Custom Footer')
    })
})
