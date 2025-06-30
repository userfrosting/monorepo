import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { UserDeleteResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

/**
 * API Composable
 */
export function useUserDeleteApi() {
    // Form data
    const loadingState = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function deleteUser(user_name: string) {
        loadingState.value = true
        apiError.value = null
        return axios
            .delete<UserDeleteResponse>('/api/users/u/' + user_name)
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
                loadingState.value = false
            })
    }

    return { loadingState, apiError, deleteUser }
}
