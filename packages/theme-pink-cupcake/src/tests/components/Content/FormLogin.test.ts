import { mount, config } from '@vue/test-utils'
import { describe, test, afterEach, expect, vi } from 'vitest'
import FormLogin from '../../../components/Pages/Account/FormLogin.vue'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import UFAlert from '../../../components/UFAlert.vue'
import UIkit from 'uikit'
import type { LoginRequest } from '@userfrosting/sprinkle-account/interfaces'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'

// Register the UFAlert component stub globally
config.global.stubs['UFAlert'] = UFAlert
config.global.stubs['FontAwesomeIcon'] = { template: '<span></span>' }

// Mock the auth store
vi.mock('@userfrosting/sprinkle-account/stores')
const mockUseAuthStore = {
    login: vi.fn()
}

// Login form data
const form: LoginRequest = {
    user_name: 'john.doe',
    password: 'password'
}

// UiKit notification data
const uikitNotification = {
    message: 'Welcome back John Doe!',
    status: 'primary',
    pos: 'top-right',
    timeout: 4000
}

describe('FormLogin.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    test('renders correctly', () => {
        const wrapper = mount(FormLogin)
        expect(wrapper.exists()).toBe(true)
    })

    test('handles successful login', async () => {
        mockUseAuthStore.login.mockResolvedValueOnce({ message: 'Welcome back John Doe!' })
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)
        vi.spyOn(UIkit, 'notification')

        const wrapper = mount(FormLogin)
        await (wrapper.vm as any).sendLogin(form)

        // Spy on the authStore & UIkit notification method
        expect(useAuthStore).toHaveBeenCalled()
        expect(mockUseAuthStore.login).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledWith(uikitNotification)
    })

    test('handles login failure', async () => {
        const error: AlertInterface = { title: 'Invalid credentials' }
        mockUseAuthStore.login.mockRejectedValueOnce(error)
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)
        vi.spyOn(UIkit, 'notification')

        const wrapper = mount(FormLogin)
        await (wrapper.vm as any).sendLogin(form)

        // Spy on the authStore & UIkit notification method
        expect(useAuthStore).toHaveBeenCalled()
        expect(mockUseAuthStore.login).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).not.toHaveBeenCalled()
        expect(wrapper.find('[data-test="error"]').exists()).toBe(true)
        expect(wrapper.get('[data-test="error"]').text()).toMatch('Invalid credentials')
    })

    test('manages loading state correctly', async () => {
        mockUseAuthStore.login.mockResolvedValueOnce({ full_name: 'John Doe' })
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)

        const wrapper = mount(FormLogin).vm as any
        expect(wrapper.loading).toBe(false)
        const sendLoginPromise = wrapper.sendLogin(form)
        expect(wrapper.loading).toBe(true)
        await sendLoginPromise
        expect(wrapper.loading).toBe(false)
    })

    test('Handle login using the v-model', async () => {
        mockUseAuthStore.login.mockResolvedValueOnce({ message: 'Welcome back John Doe!' })
        vi.mocked(useAuthStore).mockReturnValue(mockUseAuthStore as any)
        vi.spyOn(UIkit, 'notification')

        const wrapper = mount(FormLogin)
        wrapper.find('[data-test="username"]').setValue('john.doe')
        wrapper.find('[data-test="password"]').setValue('password')
        await wrapper.find('form').trigger('submit')

        // Spy on the authStore & UIkit notification method
        expect(useAuthStore).toHaveBeenCalled()
        expect(mockUseAuthStore.login).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledTimes(1)
        expect(UIkit.notification).toHaveBeenCalledWith(uikitNotification)
    })
})
