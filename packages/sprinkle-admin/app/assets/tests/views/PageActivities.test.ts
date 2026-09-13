import { mount } from '@vue/test-utils'
import { describe, expect, test } from 'vitest'
import PageActivities from '../../views/PageActivities.vue'

describe('views/PageActivities.vue', () => {
    test('renders the activities page wrapper component', () => {
        const wrapper = mount(PageActivities, {
            global: {
                stubs: {
                    UFAdminActivitiesPage: {
                        template: '<div data-test="activities-page" />'
                    }
                }
            }
        })

        expect(wrapper.find('[data-test="activities-page"]').exists()).toBe(true)
    })
})
