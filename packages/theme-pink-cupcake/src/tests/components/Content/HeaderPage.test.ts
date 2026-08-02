import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import HeaderPage from '../../../components/Content/HeaderPage.vue'

const page = {
    title: '',
    description: '',
    hideTitle: false,
    hideBreadcrumbs: false,
    breadcrumbs: [] as Array<{ label: string; to: string }>
}

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    usePageMeta: () => page
}))

describe('HeaderPage.vue', () => {
    test('hides header when no visible metadata exists', () => {
        page.title = ''
        page.description = ''
        page.hideTitle = false
        page.hideBreadcrumbs = false
        page.breadcrumbs = []

        const wrapper = mount(HeaderPage, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: ['router-link', 'font-awesome-icon']
            }
        })

        expect(wrapper.html()).toBe('<!--v-if-->')
    })

    test('shows title, description and breadcrumbs when enabled', () => {
        page.title = 'PAGE.TITLE'
        page.description = 'PAGE.DESCRIPTION'
        page.hideTitle = false
        page.hideBreadcrumbs = false
        page.breadcrumbs = [
            { label: 'HOME', to: '/' },
            { label: 'SECTION', to: '/section' },
            { label: 'PAGE.TITLE', to: '/page' }
        ]

        const wrapper = mount(HeaderPage, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    'router-link': { template: '<a><slot /></a>' },
                    'font-awesome-icon': { template: '<span data-test="home-icon" />' }
                }
            }
        })

        expect(wrapper.text()).toContain('PAGE.TITLE')
        expect(wrapper.text()).toContain('PAGE.DESCRIPTION')
        expect(wrapper.find('[data-test="home-icon"]').exists()).toBe(true)
        expect(wrapper.find('nav[aria-label="Breadcrumb"]').exists()).toBe(true)
        expect(wrapper.get('span[aria-current="page"]').text()).toBe('PAGE.TITLE')
        expect(wrapper.findAll('router-link')).toHaveLength(0)
        expect(wrapper.text()).toContain('SECTION')
    })

    test('suppresses title/description and breadcrumbs when hidden', () => {
        page.title = 'PAGE.TITLE'
        page.description = 'PAGE.DESCRIPTION'
        page.hideTitle = true
        page.hideBreadcrumbs = true
        page.breadcrumbs = [
            { label: 'HOME', to: '/' },
            { label: 'PAGE.TITLE', to: '/page' }
        ]

        const wrapper = mount(HeaderPage, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: ['router-link', 'font-awesome-icon']
            }
        })

        expect(wrapper.find('h3').exists()).toBe(false)
        expect(wrapper.find('p.uk-text-meta').exists()).toBe(false)
        expect(wrapper.find('nav').exists()).toBe(false)
    })
})
