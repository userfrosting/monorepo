import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import Page401Unauthorized from '../../views/Page401Unauthorized.vue'
import Page403Forbidden from '../../views/Page403Forbidden.vue'
import Page404NotFound from '../../views/Page404NotFound.vue'
import PageError from '../../views/PageError.vue'

describe('errorPages', () => {
    test('exports all error views', () => {
        expect(Page401Unauthorized).toBeDefined()
        expect(Page403Forbidden).toBeDefined()
        expect(Page404NotFound).toBeDefined()
        expect(PageError).toBeDefined()
    })

    test('renders all views with expected UFErrorPage props', () => {
        const createWrapper = (component: any) =>
            mount(component, {
                global: {
                    stubs: {
                        UFErrorPage: {
                            props: ['errorCode'],
                            template: '<div data-test="uf-error-page">{{ errorCode }}</div>'
                        }
                    }
                }
            })

        const wrapper401 = createWrapper(Page401Unauthorized)
        const wrapper403 = createWrapper(Page403Forbidden)
        const wrapper404 = createWrapper(Page404NotFound)
        const wrapperError = createWrapper(PageError)

        expect(wrapper401.get('[data-test="uf-error-page"]').text()).toBe('401')
        expect(wrapper403.get('[data-test="uf-error-page"]').text()).toBe('403')
        expect(wrapper404.get('[data-test="uf-error-page"]').text()).toBe('404')
        expect(wrapperError.get('[data-test="uf-error-page"]').text()).toBe('')
    })
})
