import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiErrorResponse, ApiResponse } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation - See comment in the PHP action. The API needs to be
// split into sub-api with their own schema first.
// 'schema://requests/user/edit-field.yaml'

/**
 * API Composable
 */
export function useUserUpdateApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitUserUpdate(user_name: string, fieldName: string, formData: any) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .put<ApiResponse>('/api/users/u/' + user_name + '/' + fieldName, formData)
            .then((response) => {
                useAlertsStore().push({
                    ...{ style: Severity.Success },
                    ...response.data
                })

                return response.data
            })
            .catch((err) => {
                apiError.value = err.response.data
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { submitUserUpdate, apiLoading, apiError }
}
