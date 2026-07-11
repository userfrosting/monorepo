import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { RegisterRequest } from '../../interfaces'
import { useRegisterApi } from '../../composables'

vi.mock('@regle/core', () => ({
    createRule: (rule: unknown) => rule,
    useRegle: (formData: { value: { user_name: string } }, rules: any) => {
        if (rules?.user_name?.usernameRule) {
            const usernameRule = rules.user_name.usernameRule
            const usernameState: {
                $invalid: boolean
                $errors: Array<{ $message?: string }>
                $validate: () => Promise<boolean>
            } = {
                $invalid: false,
                $errors: [],
                async $validate() {
                    const result = await usernameRule.validator(formData.value.user_name)
                    usernameState.$invalid = !result.$valid
                    usernameState.$errors = usernameState.$invalid
                        ? [{ $message: usernameRule.message(result) }]
                        : []

                    return !usernameState.$invalid
                }
            }

            return {
                r$: {
                    user_name: usernameState
                }
            }
        }

        return { r$: {} }
    }
}))

const mockUseAlertsStorePush = vi.fn()

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: () => ({
        get: vi.fn().mockImplementation((key: string, fallback?: unknown) => {
            if (key === 'locales.available') {
                return {
                    en_US: 'English',
                    fr_CA: 'French (Canada)'
                }
            }
            if (key === 'site.password.length.min') return 8
            if (key === 'site.password.length.max') return 32
            if (key === 'site.registration.user_defaults.locale') return 'fr_CA'

            return fallback
        })
    }),
    useTranslator: () => ({
        translate: vi.fn((key: string) => key)
    }),
    useAlertsStore: () => ({
        push: mockUseAlertsStorePush
    })
}))

vi.mock('@userfrosting/sprinkle-core/composables', async () => {
    const actualModule = await vi.importActual('@userfrosting/sprinkle-core/composables')

    return {
        ...actualModule,
        useRuleSchemaAdapter: () => ({
            adapt: vi.fn().mockReturnValue({})
        })
    }
})

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

describe('useRegisterApi', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    test('returns the default form', () => {
        const { defaultRegistrationForm } = useRegisterApi()

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

    test('returns available locales and captcha URL', () => {
        const { availableLocales, captchaUrl } = useRegisterApi()

        expect(availableLocales()).toEqual({
            en_US: 'English',
            fr_CA: 'French (Canada)'
        })
        expect(captchaUrl()).toBe('/account/captcha')
    })

    test('submits registration successfully', async () => {
        const { submitRegistration, apiLoading, apiError } = useRegisterApi()
        const response = {
            data: {
                title: 'Registration successful',
                description: 'Welcome!'
            }
        }
        vi.spyOn(axios, 'post').mockResolvedValue(response as any)

        expect(apiLoading.value).toBe(false)
        await submitRegistration(form)

        expect(apiLoading.value).toBe(false)
        expect(apiError.value).toBeNull()
        expect(axios.post).toHaveBeenCalledWith('/account/register', form)
        expect(mockUseAlertsStorePush).toHaveBeenCalledWith({
            title: 'Registration successful',
            description: 'Welcome!',
            style: Severity.Success
        })
    })

    test('throws and sets apiError when registration fails with API data', async () => {
        const { submitRegistration, apiError } = useRegisterApi()
        vi.spyOn(axios, 'post').mockRejectedValue({
            response: {
                data: {
                    description: 'Registration failed'
                }
            }
        })

        await expect(submitRegistration(form)).rejects.toEqual({
            description: 'Registration failed',
            style: Severity.Danger
        })

        expect(apiError.value).toEqual({
            description: 'Registration failed',
            style: Severity.Danger
        })
    })

    test('throws and sets apiError when registration fails without response', async () => {
        const { submitRegistration, apiError } = useRegisterApi()
        vi.spyOn(axios, 'post').mockRejectedValue(new Error('Network Error'))

        await expect(submitRegistration(form)).rejects.toEqual({
            description: 'Network Error',
            style: Severity.Danger
        })

        expect(apiError.value).toEqual({
            description: 'Network Error',
            style: Severity.Danger
        })
    })

    test('suggests username successfully', async () => {
        const { suggestUsername } = useRegisterApi()
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: { user_name: 'SuggestedUser' }
        } as any)

        await expect(suggestUsername()).resolves.toBe('SuggestedUser')
        expect(axios.get).toHaveBeenCalledWith('/account/suggest-username')
    })

    test('throws and sets apiError when suggesting username fails with API data', async () => {
        const { suggestUsername, apiError } = useRegisterApi()
        vi.spyOn(axios, 'get').mockRejectedValue({
            response: {
                data: {
                    description: 'Suggest failed'
                }
            }
        })

        await expect(suggestUsername()).rejects.toEqual({
            description: 'Suggest failed',
            style: Severity.Danger
        })

        expect(apiError.value).toEqual({
            description: 'Suggest failed',
            style: Severity.Danger
        })
    })

    test('throws and sets apiError when suggesting username fails without response', async () => {
        const { suggestUsername, apiError } = useRegisterApi()
        vi.spyOn(axios, 'get').mockRejectedValue(new Error('Network Error'))

        await expect(suggestUsername()).rejects.toEqual({
            description: 'Network Error',
            style: Severity.Danger
        })

        expect(apiError.value).toEqual({
            description: 'Network Error',
            style: Severity.Danger
        })
    })

    test('validates username successfully', async () => {
        const { validateUsername } = useRegisterApi()
        const validationResponse = { available: true, message: 'Available' }
        vi.spyOn(axios, 'get').mockResolvedValue({ data: validationResponse } as any)

        await expect(validateUsername('JohnDoe')).resolves.toEqual(validationResponse)
        expect(axios.get).toHaveBeenCalledWith('/account/check-username', {
            params: { user_name: 'JohnDoe' }
        })
    })

    test('executes username rule through r$username validation', async () => {
        const { r$username, formData } = useRegisterApi()
        const ruleModel = r$username as any
        vi.spyOn(axios, 'get').mockResolvedValue({
            data: {
                available: false,
                message: 'Username already used'
            }
        } as any)

        formData.value.user_name = 'TakenName'
        await ruleModel.user_name.$validate()

        expect(ruleModel.user_name.$invalid).toBe(true)
        expect(axios.get).toHaveBeenCalledWith('/account/check-username', {
            params: { user_name: 'TakenName' }
        })
    })

    test('sets password min and max length from config', () => {
        const { passwordMinLength, passwordMaxLength } = useRegisterApi()

        expect(passwordMinLength.value).toBe(8)
        expect(passwordMaxLength.value).toBe(32)
    })
})
