import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import UFFormValidationError from '../../../components/Form/UFFormValidationError.vue'

describe('UFFormValidationError.vue', () => {
    test('renders all validation errors as html', () => {
        const wrapper = mount(UFFormValidationError, {
            props: {
                errors: ['Error one', '<strong>Error two</strong>']
            }
        })

        const nodes = wrapper.findAll('.uk-form-label.uk-text-danger')
        expect(nodes).toHaveLength(2)
        expect(nodes[0].html()).toContain('Error one')
        expect(nodes[1].html()).toContain('<strong>Error two</strong>')
    })
})
