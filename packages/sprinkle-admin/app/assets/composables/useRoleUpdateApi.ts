import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiResponse, ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import type { RolePermissionsRequest } from '../interfaces'

/**
 * API used to update role permissions.
 *
 * This API is tied to the `RolePermissionsAction` API, accessed at the
 * PUT `/api/roles/r/{slug}/permissions` endpoint.
 */
export function useRoleUpdateApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitRolePermissions(slug: string, data: RolePermissionsRequest) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .put<ApiResponse>('/api/roles/r/' + slug + '/permissions', data)
            .then((response) => {
                useAlertsStore().push({
                    ...response.data,
                    style: Severity.Success
                })

                return response.data
            })
            .catch((err) => {
                apiError.value = err.response.data
                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { submitRolePermissions, apiLoading, apiError }
}
