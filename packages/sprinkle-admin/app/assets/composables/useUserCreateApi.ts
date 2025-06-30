import { ref } from 'vue'
import axios from 'axios'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { UserCreateRequest, UserCreateResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/user/create.yaml'

/**
 * API Composable
 */
export function useUserCreateApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitUserCreate(data: UserCreateRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<UserCreateResponse>('/api/users', data)
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

    return { submitUserCreate, apiLoading, apiError }
}
