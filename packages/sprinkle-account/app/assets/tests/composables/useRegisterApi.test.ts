import { afterEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore, useConfigStore } from '@userfrosting/sprinkle-core/stores'
import type { RegisterRequest } from '../../interfaces'
import { useRegisterApi } from '../../composables'

const { submitRegistration, defaultRegistrationForm, availableLocales, captchaUrl, apiLoading } =
    useRegisterApi()

const form: RegisterRequest = {
    first_name: 'John',
    last_name: 'Doe',
    email: 'john.doe@example.com',
    user_name: 'JohnDoe',
    password: 'password',
    passwordc: 'password',
    locale: 'en_US',
    captcha: 'captcha',
    spiderbro: 'http://'
}

// Mock the config & alert stores
vi.mock('@userfrosting/sprinkle-core/stores')
const mockUseConfigStore = {
    get: vi.fn()
}
const mockUseAlertsStore = {
    push: vi.fn()
}

describe('register', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('should return default form', () => {
        // Arrange
        mockUseConfigStore.get.mockReturnValue('fr_CA')
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        // Act
        const result = defaultRegistrationForm()

        // Assert
        expect(useConfigStore).toHaveBeenCalled()
        expect(mockUseConfigStore.get).toHaveBeenCalledWith(
            'site.registration.user_defaults.locale',
            'en_US'
        )
        expect(result).toEqual({
            first_name: '',
            last_name: '',
            email: '',
            user_name: '',
            password: '',
            passwordc: '',
            locale: 'fr_CA',
            captcha: '',
            spiderbro: 'http://'
        })
    })

    test('should return available locales', () => {
        // Arrange
        const locales = ['en_US', 'fr_FR', 'es_ES']
        mockUseConfigStore.get.mockReturnValue(locales)
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        // Act
        const result = availableLocales()

        // Assert
        expect(useConfigStore).toHaveBeenCalled()
        expect(mockUseConfigStore.get).toHaveBeenCalledWith('locales.available')
        expect(result).toEqual(locales)
    })

    test('should return captcha URL', () => {
        expect(captchaUrl()).toBe('/account/captcha')
    })

    test('should register successfully', async () => {
        // Arrange
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)
        const response = { data: { title: 'Registration successful' } }
        vi.spyOn(axios, 'post').mockResolvedValue(response as any)

        // Act
        await submitRegistration(form)

        // Assert
        expect(axios.post).toHaveBeenCalledWith('/account/register', form)
        expect(mockUseAlertsStore.push).toHaveBeenCalledWith({
            title: 'Registration successful',
            style: Severity.Success
        })
    })

    test('should throw an error when registration fails', async () => {
        // Arrange
        const error = { response: { data: { description: 'Registration failed' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error as any)

        // Act & Assert
        await expect(submitRegistration(form)).rejects.toEqual({
            description: 'Registration failed',
            style: Severity.Danger
        })
        expect(axios.post).toHaveBeenCalledWith('/account/register', form)
    })

    test('should set loading state to true', async () => {
        // Arrange
        const response = { data: { title: 'Registration successful' } }
        vi.spyOn(axios, 'post').mockResolvedValue(response)
        vi.mocked(useAlertsStore).mockReturnValue(mockUseAlertsStore as any)

        // Act
        expect(apiLoading.value).toBe(false)
        const submitPromise = submitRegistration(form)
        expect(apiLoading.value).toBe(true)
        await submitPromise
        expect(apiLoading.value).toBe(false)
    })
})
