import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import type { RegisterRequest, RegisterResponse } from '../interfaces'

/**
 * API Composable
 */
export function useRegisterApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

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

    function availableLocales(): string[] {
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
                return response.data
            })
            .catch((err) => {
                apiError.value = {
                    ...{
                        description: 'An error as occurred',
                        style: Severity.Danger,
                        closeBtn: true
                    },
                    ...err.response.data
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
        captchaUrl,
        apiLoading,
        apiError
    }
}
