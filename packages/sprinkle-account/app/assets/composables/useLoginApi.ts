import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import type { LoginRequest, LoginResponse } from '../interfaces'
import { useAuthStore } from '../stores'
import { useCsrf } from '@userfrosting/sprinkle-core/composables'

/**
 * API Composable
 */
export function useLoginApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    /**
     * Get the default form for the login
     */
    function defaultFormData(): LoginRequest {
        return {
            user_name: '',
            password: '',
            rememberme: false
        }
    }

    async function submitLogin(data: LoginRequest) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .post<LoginResponse>('/account/login', data)
            .then((response) => {
                // Add success message to the alerts store
                useAlertsStore().push({
                    title: response.data.message,
                    style: Severity.Success
                })

                // Set the user in the auth store
                useAuthStore().setUser(response.data.user)

                // Update the CSRF token
                useCsrf().updateFromHeaders(response.headers)
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
        submitLogin,
        defaultFormData,
        apiLoading,
        apiError
    }
}
