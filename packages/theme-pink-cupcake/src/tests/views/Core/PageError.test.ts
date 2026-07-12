import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import PageError from '../../../views/Core/PageError.vue'

const pageMeta = { hideTitle: false, hideBreadcrumbs: false }

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    usePageMeta: () => pageMeta
}))

describe('PageError.vue', () => {
    test('sets page meta and maps known error code props', () => {
        pageMeta.hideTitle = false
        pageMeta.hideBreadcrumbs = false

        const wrapper = mount(PageError, {
            props: { errorCode: 404 },
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                stubs: {
                    UFInfoBox: {
                        props: ['faIcon', 'value', 'label'],
                        template:
                            '<div data-test="info" :data-icon="faIcon" :data-value="value" :data-label="label"></div>'
                    }
                }
            }
        })

        const info = wrapper.get('[data-test="info"]')
        expect(pageMeta.hideTitle).toBe(true)
        expect(pageMeta.hideBreadcrumbs).toBe(true)
        expect(info.attributes('data-icon')).toBe('magnifying-glass')
        expect(info.attributes('data-value')).toBe('ERROR.404.TITLE')
        expect(info.attributes('data-label')).toBe('ERROR.404.DESCRIPTION')
    })

    test('uses default icon and message for unknown error codes', () => {
        const wrapper = mount(PageError, {
            props: { errorCode: 999 },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFInfoBox: {
                        props: ['faIcon', 'value', 'label'],
                        template:
                            '<div data-test="info" :data-icon="faIcon" :data-value="value" :data-label="label"></div>'
                    }
                }
            }
        })

        const info = wrapper.get('[data-test="info"]')
        expect(info.attributes('data-icon')).toBe('triangle-exclamation')
        expect(info.attributes('data-value')).toBe('ERROR.TITLE')
        expect(info.attributes('data-label')).toBe('ERROR.DESCRIPTION')
    })

    test('maps additional known error codes to specific title/description and icons', () => {
        const cases = [
            { code: 401, icon: 'lock' },
            { code: 403, icon: 'ban' },
            { code: 400, icon: 'triangle-exclamation' },
            { code: 405, icon: 'triangle-exclamation' },
            { code: 410, icon: 'triangle-exclamation' }
        ]

        for (const c of cases) {
            const wrapper = mount(PageError, {
                props: { errorCode: c.code },
                global: {
                    mocks: { $t: (key: string) => key },
                    stubs: {
                        UFInfoBox: {
                            props: ['faIcon', 'value', 'label'],
                            template:
                                '<div data-test="info" :data-icon="faIcon" :data-value="value" :data-label="label"></div>'
                        }
                    }
                }
            })

            const info = wrapper.get('[data-test="info"]')
            expect(info.attributes('data-icon')).toBe(c.icon)
            expect(info.attributes('data-value')).toBe(`ERROR.${c.code}.TITLE`)
            expect(info.attributes('data-label')).toBe(`ERROR.${c.code}.DESCRIPTION`)
        }
    })
})
