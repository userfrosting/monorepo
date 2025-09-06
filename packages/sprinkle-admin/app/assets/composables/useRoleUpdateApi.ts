import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiResponse, ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation - This composable is only used to associates the
// permissions with the role. It should have a dedicated schema for this, plus
// be merged with 'useRolePermissionsApi'
// 'schema://requests/role/edit-field.yaml'

/**
 * API used to update role.
 *
 * This API is tied to the `RoleUpdateFieldAction` API, accessed at the
 * GET `/api/roles/r/{slug}/{field}` endpoint.
 *
 * This composable can be used to update {field} for a specific role.
 */
export function useRoleUpdateApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitRoleUpdate(slug: string, fieldName: string, formData: any) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .put<ApiResponse>('/api/roles/r/' + slug + '/' + fieldName, formData)
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

    return { submitRoleUpdate, apiLoading, apiError }
}
