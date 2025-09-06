import { ref } from 'vue'
import axios from 'axios'
import {
    type ApiResponse,
    Severity,
    type AlertInterface
} from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useAuthStore } from '../stores'
import { useCsrf } from '@userfrosting/sprinkle-core/composables'

/**
 * API Composable
 */
export function useLogoutApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function submitLogout() {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get<ApiResponse>('/account/logout')
            .then((response) => {
                // Add success message to the alerts store
                useAlertsStore().push({
                    title: response.data.title,
                    description: response.data.description,
                    style: Severity.Success
                })

                // Set the user in the auth store
                useAuthStore().unsetUser()

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
        submitLogout,
        apiLoading,
        apiError
    }
}
