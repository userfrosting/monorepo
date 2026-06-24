import { ref } from 'vue'
import axios from 'axios'
import { type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { UserRoleSprunjeResponse } from '../interfaces'

/**
 * API used to fetch a match between all available roles and the user's role,
 * in a single component
 *
 * This API is tied to the `UserRoleSprunje` API, accessed at
 * the GET `/api/users/u/{username}/roles` endpoint.
 *
 * This composable accept a {username} to select the roles of a specific user.
 */
export function useUserRolesApi() {
    const loading = ref<boolean>(false)
    const error = ref<ApiErrorResponse | null>()
    const selected = ref<number[]>([])

    // Fetch role permissions and match them with the permissions
    async function fetch(username: string) {
        axios
            .get<UserRoleSprunjeResponse>('/api/users/u/' + username + '/roles')
            .then((response) => {
                selected.value = response.data.rows.map((role) => role.id)
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
