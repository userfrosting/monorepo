import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore, useTranslator } from '@userfrosting/sprinkle-core/stores'
import type { ApiResponse, ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { ProfileEditRequest } from '../interfaces'

// TODO : Add validation
// 'schema://requests/profile-settings.yaml'

/**
 * API Composable
 */
export function useUserProfileEditApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitProfileEdit(data: ProfileEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ApiResponse>('/account/settings/profile', data)
            .then((response) => {
                // Reload the translator dictionary to reflect the user's language
                useTranslator().load()

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

    return { submitProfileEdit, apiLoading, apiError }
}
