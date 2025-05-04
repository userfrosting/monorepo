import { expect, test, describe, vi, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import NavBarLogin from '../../../components/NavBar/NavBarLogin.vue'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'

// Mock the config and pageMeta store
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseConfigStore = {
    get: vi.fn()
}

describe('NavBarLogin.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('Render correctly', () => {
        // Custom mock the config store
        mockUseConfigStore.get.mockReturnValue(true)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const wrapper = mount(NavBarLogin, {
            global: {
                stubs: ['router-link', 'FormLogin']
            }
        })
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('[data-test="gotoRegister"]').exists()).toBe(true)
    })

    test('Render registration is removed if disabled', () => {
        // Custom mock the config store
        mockUseConfigStore.get.mockReturnValue(false)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const wrapper = mount(NavBarLogin, {
            global: {
                stubs: ['router-link', 'FormLogin']
            }
        })
        expect(wrapper.exists()).toBe(true)
        expect(wrapper.find('[data-test="gotoRegister"]').exists()).toBe(false)
    })
})
