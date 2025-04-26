import { afterEach, beforeEach, expect, test, describe, vi } from 'vitest'
import { mount, config } from '@vue/test-utils'
import { library } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import PageLogin from '../../views/Account/PageLogin.vue'
import CardBoxBig from '../../components/Content/CardBoxBig.vue'
import CardBox from '../../components/Content/CardBox.vue'
import UFAlert from '../../components/UFAlert.vue'

// Register font-awesome-icon component
library.add(fas)
config.global.stubs['FontAwesomeIcon'] = FontAwesomeIcon

// Register global components
config.global.components['UFCardBoxBig'] = CardBoxBig
config.global.components['UFCardBox'] = CardBox
config.global.components['UFAlert'] = UFAlert

// Mock the config store
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseConfigStore = {
    get: vi.fn()
}

describe('PageLogin.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    beforeEach(() => {
        // Mock the config store
        mockUseConfigStore.get.mockReturnValue(true)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)
    })

    test('Render correctly', () => {
        const wrapper = mount(PageLogin)
        expect(wrapper.exists()).toBe(true)
    })

    test('Emits gotoForgotPassword event', async () => {
        const wrapper = mount(PageLogin)
        await wrapper.find('[data-test="gotoForgotPassword"]').trigger('click')
        expect(wrapper.emitted('gotoForgotPassword')).toBeTruthy()
    })

    test('Emits gotoVerification event', async () => {
        const wrapper = mount(PageLogin)
        expect(wrapper.find('[data-test="gotoVerification"]').exists()).toBe(true)
        await wrapper.find('[data-test="gotoVerification"]').trigger('click')
        expect(wrapper.emitted('gotoVerification')).toBeTruthy()
    })

    test('gotoVerification is not available if verification is disabled', async () => {
        // Custom mock the config store
        mockUseConfigStore.get.mockReturnValue(false)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        const wrapper = mount(PageLogin)
        expect(wrapper.find('[data-test="gotoVerification"]').exists()).toBe(false)
    })

    test('Emits gotoRegistration event', async () => {
        const wrapper = mount(PageLogin)
        await wrapper.find('[data-test="gotoRegistration"]').trigger('click')
        expect(wrapper.emitted('gotoRegistration')).toBeTruthy()
    })
})
