import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import UFLabel from '../../components/UFLabel.vue'

describe('UFLabel.vue', () => {
    test('renders default primary class', () => {
        const wrapper = mount(UFLabel, {
            slots: { default: 'Label' }
        })

        expect(wrapper.classes()).toContain('uk-label')
        expect(wrapper.classes()).toContain('uk-label-primary')
        expect(wrapper.text()).toBe('Label')
    })

    test.each([
        [Severity.Success, 'uk-label-success'],
        [Severity.Warning, 'uk-label-warning'],
        [Severity.Danger, 'uk-label-danger'],
        [Severity.Info, 'uk-label-info'],
        [Severity.Muted, 'uk-label-muted'],
        [Severity.Secondary, 'uk-label-secondary'],
        [Severity.Primary, 'uk-label-primary']
    ])('maps %s to %s', (severity, cssClass) => {
        const wrapper = mount(UFLabel, {
            props: { severity }
        })

        expect(wrapper.classes()).toContain(cssClass)
    })
})
