import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { UserEditRequest, UserEditResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/user/edit-info.yaml'

/**
 * API Composable
 */
export function useUserEditApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function submitUserEdit(user_name: string, data: UserEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<UserEditResponse>('/api/users/u/' + user_name, data)
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

    return { submitUserEdit, apiLoading, apiError }
}
