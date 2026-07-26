import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import UFModal from '../../../components/Modals/UFModal.vue'

describe('UFModal.vue', () => {
    test('renders modal container with default props and default slot', () => {
        const wrapper = mount(UFModal, {
            slots: {
                default: 'Modal body content'
            }
        })

        const modal = wrapper.get('[uk-modal]')
        expect(modal.attributes('esc-close')).toBe('true')
        expect(modal.attributes('bg-close')).toBe('true')
        expect(wrapper.find('.uk-modal-close-default').exists()).toBe(false)
        expect(wrapper.find('.uk-modal-header').exists()).toBe(false)
        expect(wrapper.find('.uk-modal-footer').exists()).toBe(false)
        expect(wrapper.get('.uk-modal-body').text()).toContain('Modal body content')
    })

    test('renders close button, header slot and footer slot when provided', () => {
        const wrapper = mount(UFModal, {
            props: {
                closable: true,
                escClose: false,
                bgClose: false
            },
            slots: {
                header: 'Modal title',
                default: 'Modal content',
                footer: 'Modal actions'
            }
        })

        const modal = wrapper.get('[uk-modal]')
        expect(modal.attributes('esc-close')).toBe('false')
        expect(modal.attributes('bg-close')).toBe('false')
        expect(wrapper.find('.uk-modal-close-default').exists()).toBe(true)
        expect(wrapper.get('.uk-modal-header .uk-modal-title').text()).toContain('Modal title')
        expect(wrapper.get('.uk-modal-body').text()).toContain('Modal content')
        expect(wrapper.get('.uk-modal-footer').text()).toContain('Modal actions')
    })
})
