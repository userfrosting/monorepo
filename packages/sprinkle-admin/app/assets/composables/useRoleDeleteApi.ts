import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { RoleDeleteResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

/**
 * API Composable
 */
export function useRoleDeleteApi() {
    // Form data
    const loadingState = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function deleteRole(slug: string) {
        loadingState.value = true
        apiError.value = null
        return axios
            .delete<RoleDeleteResponse>('/api/roles/r/' + slug)
            .then((response) => {
                // Add the message to the alert stream
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

    return { loadingState, apiError, deleteRole }
}
