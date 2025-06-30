import { ref } from 'vue'
import axios from 'axios'
import { type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import type { RolesSprunjeResponse, UserRoleSprunjeResponse } from '../interfaces'

/**
 * API used to fetch a match between all available roles and the user's role,
 * in a single component
 *
 * This API is tied to the `RolesSprunje` and `UserRoleSprunje` API, accessed at
 * the GET `/api/roles` and `/api/users/u/{username}/roles` endpoints.
 *
 * This composable accept a {username} to select the roles of a specific user.
 */
export function useUserRolesApi() {
    const loading = ref(false)
    const error = ref<ApiErrorResponse | null>()
    const selected = ref<Number[]>([])
    const roles = ref<RoleInterface[]>([])

    // Step 1 - Fetch all permissions
    async function fetch(username: string) {
        loading.value = true
        axios
            .get<RolesSprunjeResponse>('/api/roles')
            .then((response) => {
                roles.value = response.data.rows
                fetchUserRoles(username)
            })
            .catch((err) => {
                loading.value = false
                error.value = err.response.data
            })
    }

    // Step 2 - Fetch role permissions and match them with the permissions
    async function fetchUserRoles(username: string) {
        axios
            .get<UserRoleSprunjeResponse>('/api/users/u/' + username + '/roles')
            .then((response) => {
                // Empty the selected array
                selected.value.splice(0)

                // Match the permissions with the role permissions
                const userRoles: RoleInterface[] = response.data.rows
                userRoles.forEach((userRole) => {
                    const record = roles.value.find((element) => element.id === userRole.id)
                    if (record) {
                        selected.value.push(userRole.id)
                    }
                })
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { error, loading, fetch, selected, roles }
}
