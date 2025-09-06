import { ref } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useCsrf, useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import type { LoginRequest, LoginResponse } from '../interfaces'
import { useAuthStore } from '../stores'
import schemaFile from '../../schema/requests/login.yaml'

/**
 * API Composable
 */
export function useLoginApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<AlertInterface | null>(null)
    const formData = ref<LoginRequest>(defaultFormData())

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

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
        apiError,
        formData,
        r$
    }
}
