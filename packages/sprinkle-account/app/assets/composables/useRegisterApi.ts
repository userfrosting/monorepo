import { ref } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore, useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import type { RegisterRequest, RegisterResponse } from '../interfaces'
import schemaFile from '../../schema/requests/register.yaml?raw'

/**
 * API Composable
 */
export function useRegisterApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)
    const passwordMinLength = ref<number>(0)
    const passwordMaxLength = ref<number>(0)
    const formData = ref<RegisterRequest>(defaultRegistrationForm())

    // Retrieve min/max password length from site settings and update validator
    // constraints
    const config = useConfigStore()
    passwordMinLength.value = config.get('site.password.length.min')
    passwordMaxLength.value = config.get('site.password.length.max')

    // TODO : Pass min/max to Regle (can't change defined regle, the message won't follow)
    // TODO : matches rules is not implemented

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    /**
     * Get the default form for the registration
     */
    function defaultRegistrationForm(): RegisterRequest {
        const config = useConfigStore()
        return {
            first_name: '',
            last_name: '',
            email: '',
            user_name: '',
            password: '',
            passwordc: '',
            locale: config.get('site.registration.user_defaults.locale', 'en_US'),
            captcha: '',
            spiderbro: 'http://'
        }
    }

    function availableLocales(): Record<string, string> {
        return useConfigStore().get('locales.available')
    }

    function captchaUrl(): string {
        return '/account/captcha' // TODO : Add captcha path to config
    }

    async function submitRegistration(data: RegisterRequest) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .post<RegisterResponse>('/account/register', data)
            .then((response) => {
                // Add success message to the alerts store
                useAlertsStore().push({
                    title: response.data.title,
                    description: response.data.description,
                    style: Severity.Success
                })
            })
            .catch((err) => {
                apiError.value = {
                    ...(err.response?.data ?? { description: err.message }),
                    style: Severity.Danger
                }

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    async function suggestUsername() {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get('/account/suggest-username')
            .then((response) => {
                return response.data.user_name
            })
            .catch((err) => {
                apiError.value = {
                    ...(err.response?.data ?? { description: err.message }),
                    style: Severity.Danger
                }

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return {
        submitRegistration,
        defaultRegistrationForm,
        availableLocales,
        suggestUsername,
        captchaUrl,
        formData,
        apiLoading,
        apiError,
        r$,
        passwordMinLength,
        passwordMaxLength
    }
}
