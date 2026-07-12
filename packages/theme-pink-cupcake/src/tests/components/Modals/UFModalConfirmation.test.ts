import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import UFModalConfirmation from '../../../components/Modals/UFModalConfirmation.vue'

describe('UFModalConfirmation.vue', () => {
    test('renders default translated prompt and emits confirm/cancel', async () => {
        const wrapper = mount(UFModalConfirmation, {
            props: {
                acceptSeverity: Severity.Warning,
                rejectSeverity: Severity.Danger,
                icon: 'triangle-exclamation'
            },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    },
                    'font-awesome-icon': { template: '<span data-test="icon" />' }
                }
            }
        })

        expect(wrapper.get('[data-test="header"]').text()).toContain('CONFIRMATION')
        expect(wrapper.get('[data-test="body"]').text()).toContain('CONFIRM_ACTION')
        expect(wrapper.get('[data-test="body"]').text()).toContain('WARNING_CANNOT_UNDONE')
        expect(wrapper.findAll('button')[0].classes()).toContain('uk-button-danger')
        expect(wrapper.findAll('button')[1].classes()).toContain('uk-button-warning')

        await wrapper.findAll('button')[0].trigger('click')
        await wrapper.findAll('button')[1].trigger('click')
        expect(wrapper.emitted('cancelled')).toHaveLength(1)
        expect(wrapper.emitted('confirmed')).toHaveLength(1)
    })

    test('supports custom slots and hidden cancel button', () => {
        const wrapper = mount(UFModalConfirmation, {
            props: {
                cancelBtn: false,
                title: null,
                warning: ''
            },
            slots: {
                header: 'Header',
                prompt: 'Prompt',
                warning: 'Warning',
                footer: 'Footer'
            },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    },
                    'font-awesome-icon': { template: '<span />' }
                }
            }
        })

        expect(wrapper.get('[data-test="header"]').text()).toContain('Header')
        expect(wrapper.get('[data-test="body"]').text()).toContain('Prompt')
        expect(wrapper.get('[data-test="footer"]').text()).toContain('Footer')
        expect(wrapper.findAll('button')).toHaveLength(0)
    })

    test('maps all severity variants to button classes', () => {
        const cases: Array<{ severity: Severity; expected: string }> = [
            { severity: Severity.Success, expected: 'uk-button-success' },
            { severity: Severity.Info, expected: 'uk-button-info' },
            { severity: Severity.Secondary, expected: 'uk-button-secondary' },
            { severity: Severity.Muted, expected: 'uk-button-muted' },
            { severity: Severity.Primary, expected: 'uk-button-primary' },
            { severity: Severity.Default, expected: 'uk-button-default' }
        ]

        for (const testCase of cases) {
            const wrapper = mount(UFModalConfirmation, {
                props: {
                    rejectSeverity: testCase.severity,
                    acceptSeverity: testCase.severity
                },
                global: {
                    mocks: { $t: (key: string) => key },
                    stubs: {
                        UFModal: {
                            template:
                                '<div><div><slot name="header" /></div><div><slot /></div><div><slot name="footer" /></div></div>'
                        },
                        'font-awesome-icon': { template: '<span />' }
                    }
                }
            })

            const buttons = wrapper.findAll('button')
            expect(buttons[0].classes()).toContain(testCase.expected)
            expect(buttons[1].classes()).toContain(testCase.expected)
        }
    })
})
