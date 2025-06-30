import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { RoleEditRequest, RoleEditResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/role/edit-info.yaml'

/**
 * API Composable
 */
export function useRoleEditApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function submitRoleEdit(slug: string, data: RoleEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<RoleEditResponse>('/api/roles/r/' + slug, data)
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
                apiLoading.value = false
            })
    }

    return { submitRoleEdit, apiLoading, apiError }
}
