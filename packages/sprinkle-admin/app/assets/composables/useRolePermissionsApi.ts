import { ref } from 'vue'
import axios from 'axios'
import type { ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { RolePermissionsSprunjeResponse } from '../interfaces'

/**
 * API used to fetch a match between all available permissions and the role's
 * permissions, in a single component
 *
 * This API is tied to the `RolePermissionsSprunje` API, accessed at
 * the GET `/api/roles/r/{slug}/permissions` endpoint.
 *
 * This composable accept a {roleSlug} to select the permissions of a specific
 * role.
 */
export function useRolePermissionsApi() {
    const loading = ref<boolean>(false)
    const error = ref<ApiErrorResponse | null>()
    const selected = ref<number[]>([])

    // Fetch role's permissions and match them with the permissions
    async function fetch(roleSlug: string) {
        axios
            .get<RolePermissionsSprunjeResponse>('/api/roles/r/' + roleSlug + '/permissions')
            .then((response) => {
                selected.value = response.data.rows.map((permission) => permission.id)
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { error, loading, fetch, selected }
}
