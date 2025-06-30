import { ref } from 'vue'
import axios from 'axios'
import {
    type ApiResponse,
    type ApiErrorResponse,
    Severity
} from '@userfrosting/sprinkle-core/interfaces'
import type { PasswordEditRequest } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/account-settings.yaml'

/**
 * API Composable
 */
export function useUserPasswordEditApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitPasswordEdit(data: PasswordEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ApiResponse>('/account/settings', data)
            .then((response) => {
                useAlertsStore().push({
                    title: response.data.title,
                    description: response.data.description,
                    style: Severity.Success
                })
            })
            .catch((err) => {
                apiError.value = err.response.data

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { submitPasswordEdit, apiLoading, apiError }
}
