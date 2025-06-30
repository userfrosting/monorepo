import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiResponse, AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { EmailEditRequest } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/account-email.yaml'

/**
 * API Composable
 */
export function useUserEmailEditApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function submitEmailEdit(data: EmailEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ApiResponse>('/account/settings/email', data)
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

    return { submitEmailEdit, apiLoading, apiError }
}
