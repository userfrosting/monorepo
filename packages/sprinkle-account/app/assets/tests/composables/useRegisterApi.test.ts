import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { RegisterRequest } from '../../interfaces'
import { useRegisterApi } from '../../composables'

const { defaultRegistrationForm, availableLocales, captchaUrl } = useRegisterApi()

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

// Mock composables
const mockUseAlertsStorePush = vi.fn()
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: () => ({
        get: vi.fn().mockImplementation((key: string) => {
            if (key === 'locales.available') return ['en_US', 'fr_FR', 'es_ES']
            if (key === 'site.password.length.min') return 8
            if (key === 'site.password.length.max') return 32
            if (key === 'site.registration.user_defaults.locale') return 'fr_CA'
            return undefined
        })
    }),
    useTranslator: () => ({
        translate: vi.fn().mockImplementation((key) => {
            return key
        })
    }),
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
    })
}))

describe('register', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    /*beforeEach(() => {
        vi.spyOn(axios, 'get').mockImplementation((url) => {
            if (url === '/account/check-username') {
                return Promise.resolve({ data: { available: true, message: 'Available' } })
            }
            // fallback to default behavior for other endpoints
            return Promise.resolve({ data: {} })
        })
    })*/

    test('should return default form', () => {
        expect(defaultRegistrationForm()).toEqual({
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

    /*test('should return available locales', () => {
        expect(availableLocales()).toEqual(['en_US', 'fr_FR', 'es_ES'])
    })

    test('should return captcha URL', () => {
        expect(captchaUrl()).toBe('/account/captcha')
    })

    test('should register successfully', async () => {
        // Arrange
        const { submitRegistration } = useRegisterApi()
        const response = { data: { title: 'Registration successful', description: 'Welcome!' } }
        vi.spyOn(axios, 'post').mockResolvedValue(response)

        // Act
        await submitRegistration(form)

        // Assert
        expect(axios.post).toHaveBeenCalledWith('/account/register', form)

        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Registration successful',
            description: 'Welcome!',
            style: Severity.Success
        })
    })

    test('should throw an error when registration fails', async () => {
        // Arrange
        const { submitRegistration, apiError } = useRegisterApi()
        const error = { response: { data: { description: 'Registration failed' } } }
        vi.spyOn(axios, 'post').mockRejectedValue(error)

        // Act & Assert
        await expect(submitRegistration(form)).rejects.toEqual({
            description: 'Registration failed',
            style: Severity.Danger
        })
        expect(axios.post).toHaveBeenCalledWith('/account/register', form)
        expect(apiError.value).toEqual({
            description: 'Registration failed',
            style: Severity.Danger
        })
    })

    test('should set loading state to true during registration', async () => {
        // Arrange
        const { submitRegistration, apiLoading } = useRegisterApi()
        const response = { data: { title: 'Registration successful', description: 'Welcome!' } }
        vi.spyOn(axios, 'post').mockResolvedValue(response)

        // Act
        expect(apiLoading.value).toBe(false)
        const submitPromise = submitRegistration(form)
        expect(apiLoading.value).toBe(true)
        await submitPromise
        expect(apiLoading.value).toBe(false)
    })

    test('should suggest a username successfully', async () => {
        // Arrange
        const { suggestUsername } = useRegisterApi()
        const mockUsername = 'SuggestedUser'
        vi.spyOn(axios, 'get').mockResolvedValue({ data: { user_name: mockUsername } })

        // Act
        const result = await suggestUsername()

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/account/suggest-username')
        expect(result).toBe(mockUsername)
    })

    test('should handle error when suggesting username', async () => {
        // Arrange
        const { suggestUsername, apiError } = useRegisterApi()
        const error = { response: { data: { description: 'Suggest failed' } } }
        vi.spyOn(axios, 'get').mockRejectedValue(error)

        // Act & Assert
        await expect(suggestUsername()).rejects.toEqual({
            description: 'Suggest failed',
            style: Severity.Danger
        })
        expect(apiError.value).toEqual({
            description: 'Suggest failed',
            style: Severity.Danger
        })
    })

    test('should validate username successfully', async () => {
        // Arrange
        const { validateUsername } = useRegisterApi()
        const username = 'JohnDoe'
        const validationResponse = { available: true, message: 'Available' }
        vi.spyOn(axios, 'get').mockResolvedValue({ data: validationResponse })

        // Act
        const result = await validateUsername(username)

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/account/check-username', {
            params: { user_name: username }
        })
        expect(result).toEqual(validationResponse)
    })*/

    test('should set password min and max length from config', () => {
        // Act
        const { passwordMinLength, passwordMaxLength } = useRegisterApi()

        // Assert
        expect(passwordMinLength.value).toBe(8)
        expect(passwordMaxLength.value).toBe(32)
    })
})
