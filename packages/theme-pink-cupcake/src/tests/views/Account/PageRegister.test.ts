import { expect, test, describe, vi, afterEach, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import PageRegister from '../../../views/Account/PageRegister.vue'
import { useConfigStore, usePageMeta } from '@userfrosting/sprinkle-core/stores'

// Mock the config and pageMeta store
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseConfigStore = {
    get: vi.fn()
}
const mockUsePageMeta = {
    hideTitle: false
}

// Mock router
const mockedPush = vi.fn()
vi.mock('vue-router', () => ({
    useRouter: vi.fn(() => ({
        push: mockedPush
    }))
}))

describe('PageRegister.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    beforeEach(() => {
        vi.mocked(usePageMeta).mockReturnValue(mockUsePageMeta as any)
    })

    test('Render correctly', () => {
        mockUseConfigStore.get.mockReturnValue(true) // True means registration is enabled
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)
        const wrapper = mount(PageRegister, {
            global: {
                stubs: [
                    'router-link',
                    'UFCardBoxLarge',
                    'UFCardBox',
                    'UFAlert',
                    'FormRegister',
                    'FontAwesomeIcon'
                ]
            }
        })
        expect(wrapper.exists()).toBe(true)
    })

    test('Redirect if registration is disabled', () => {
        mockUseConfigStore.get.mockReturnValue(false) // False means registration is disabled
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        // Mount the component
        const wrapper = mount(PageRegister, {
            global: {
                stubs: [
                    'router-link',
                    'UFCardBoxLarge',
                    'UFCardBox',
                    'UFAlert',
                    'FormRegister',
                    'FontAwesomeIcon'
                ]
            }
        })
        expect(wrapper.exists()).toBe(true)

        // Expect the router to have been called with the correct path
        expect(mockedPush).toHaveBeenCalledTimes(1)
        expect(mockedPush).toHaveBeenCalledWith({ name: 'account.login' })
    })

    test('does not redirect if registration is enabled and renders login CTA', () => {
        mockUseConfigStore.get.mockReturnValue(true)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const wrapper = mount(PageRegister, {
            global: {
                stubs: {
                    'router-link': { template: '<a v-bind="$attrs"><slot /></a>' },
                    UFCardBoxLarge: { template: '<div><slot /></div>' },
                    UFCardBox: { template: '<div><slot /></div>' },
                    UFAlert: true,
                    FormRegister: true,
                    FontAwesomeIcon: true
                }
            }
        })

        expect(mockedPush).not.toHaveBeenCalled()
        expect(wrapper.find('[data-test="gotoLogin"]').exists()).toBe(true)
    })
})
