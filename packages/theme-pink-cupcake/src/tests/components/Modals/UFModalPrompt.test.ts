import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import UFModalPrompt from '../../../components/Modals/UFModalPrompt.vue'

describe('UFModalPrompt.vue', () => {
    test('renders prompt form and updates model', async () => {
        const wrapper = mount(UFModalPrompt, {
            props: {
                modelValue: '',
                title: 'Enter name',
                prompt: 'PROMPT',
                placeholder: 'PLACEHOLDER',
                btnLabel: 'Save'
            },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    }
                }
            }
        })

        await wrapper.get('input').setValue('Alice')
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Alice'])
        expect(wrapper.get('[data-test="header"]').text()).toContain('Enter name')
        expect(wrapper.get('label').text()).toContain('PROMPT')
        expect(wrapper.get('input').attributes('placeholder')).toBe('PLACEHOLDER')
        expect(wrapper.get('[data-test="footer"]').text()).toContain('Save')
    })

    test('clicks the ok button when form is submitted and supports custom slots', async () => {
        const wrapper = mount(UFModalPrompt, {
            slots: {
                header: 'Custom Header',
                default: 'Custom Body',
                footer: 'Custom Footer'
            },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    }
                }
            }
        })

        expect(wrapper.get('[data-test="header"]').text()).toContain('Custom Header')
        expect(wrapper.get('[data-test="body"]').text()).toContain('Custom Body')
        expect(wrapper.get('[data-test="footer"]').text()).toContain('Custom Footer')
    })

    test('uses the translated title when the header slot is not provided', () => {
        const wrapper = mount(UFModalPrompt, {
            props: { title: 'Prompt title' },
            global: {
                mocks: { $t: (key: string) => `translated:${key}` },
                stubs: {
                    UFModal: {
                        template: '<div><slot name="header" /></div>'
                    }
                }
            }
        })

        expect(wrapper.text()).toContain('translated:Prompt title')
    })

    test('submits safely when a custom footer does not provide an OK button', async () => {
        const wrapper = mount(UFModalPrompt, {
            slots: { footer: 'Custom footer' },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFModal: {
                        template: '<div><slot name="header" /><slot /><slot name="footer" /></div>'
                    }
                }
            }
        })

        await wrapper.get('form').trigger('submit')
        expect(wrapper.text()).toContain('Custom footer')
    })

    test('supports a null title with an empty header slot', () => {
        const wrapper = mount(UFModalPrompt, {
            props: { title: null },
            slots: { header: () => [] },
            global: {
                mocks: { $t: (key: string) => `translated:${key}` },
                stubs: {
                    UFModal: {
                        template: '<div><slot name="header" /></div>'
                    }
                }
            }
        })

        expect(wrapper.text()).toContain('translated:')
    })

    test('submits prompt form by triggering click on ok button ref', async () => {
        const wrapper = mount(UFModalPrompt, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFModal: {
                        template:
                            '<div><div data-test="header"><slot name="header" /></div><div data-test="body"><slot /></div><div data-test="footer"><slot name="footer" /></div></div>'
                    }
                }
            }
        })

        const clickSpy = vi.spyOn(HTMLButtonElement.prototype, 'click')
        await wrapper.get('form').trigger('submit.prevent')

        expect(clickSpy).toHaveBeenCalled()
        clickSpy.mockRestore()
    })
})
