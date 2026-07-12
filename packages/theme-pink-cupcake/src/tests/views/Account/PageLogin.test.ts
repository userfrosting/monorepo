import { afterEach, beforeEach, expect, test, describe, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { useConfigStore, usePageMeta } from '@userfrosting/sprinkle-core/stores'
import PageLogin from '../../../views/Account/PageLogin.vue'

// Mock the config and pageMeta store
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseConfigStore = {
    get: vi.fn()
}
const mockUsePageMeta = {
    hideTitle: false
}

describe('PageLogin.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    beforeEach(() => {
        mockUseConfigStore.get.mockReturnValue(true)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)
        vi.mocked(usePageMeta).mockReturnValue(mockUsePageMeta as any)
    })

    test('Render correctly', () => {
        const wrapper = mount(PageLogin, {
            global: {
                stubs: [
                    'router-link',
                    'UFCardBoxLarge',
                    'UFCardBoxHalf',
                    'UFCardBox',
                    'UFAlert',
                    'FontAwesomeIcon'
                ]
            }
        })
        expect(wrapper.exists()).toBe(true)
    })

    test('gotoVerification is not available if verification is disabled', async () => {
        // Custom mock the config store
        mockUseConfigStore.get.mockReturnValue(false)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const wrapper = mount(PageLogin, {
            global: {
                stubs: [
                    'router-link',
                    'UFCardBoxLarge',
                    'UFCardBoxHalf',
                    'UFCardBox',
                    'UFAlert',
                    'FontAwesomeIcon'
                ]
            }
        })
        expect(wrapper.find('[data-test="gotoVerification"]').exists()).toBe(false)
    })

    test('shows verification link and hides register card when registration is disabled', () => {
        mockUseConfigStore.get.mockImplementation((key: string) => {
            if (key === 'site.registration.require_email_verification') return true
            if (key === 'site.registration.enabled') return false
            return false
        })
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const wrapper = mount(PageLogin, {
            global: {
                stubs: {
                    'router-link': { template: '<a v-bind="$attrs"><slot /></a>' },
                    UFCardBoxHalf: { template: '<div><slot /></div>' },
                    UFCardBox: { template: '<div><slot /></div>' },
                    UFAlert: true,
                    FormLogin: true,
                    FontAwesomeIcon: true
                }
            }
        })

        expect(wrapper.find('[data-test="gotoVerification"]').exists()).toBe(true)
        expect(wrapper.text()).not.toContain('REGISTRATION.QUESTION')
    })
})
