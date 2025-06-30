import { ref, toValue, watchEffect } from 'vue'
import axios from 'axios'
import type { ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { RoleResponse } from '../interfaces'

/**
 * API used to fetch data about a specific role.
 *
 * This interface is tied to the `RoleApi` API, accessed at the GET
 * `/api/roles/r/{slug}` endpoint and the `RoleResponse` Typescript
 * interface.
 *
 * This composable accept a {slug} to select the role. Any changes to the
 * {slug} is watched and will trigger an update.
 *
 * Available ref:
 * - role: RoleResponse
 * - error: AlertInterface | null
 * - loading: boolean
 * - fetchRole(): void - Trigger a refresh of the user data
 */
export function useRoleApi(slug: any) {
    const loading = ref(false)
    const error = ref<ApiErrorResponse | null>()
    const role = ref<RoleResponse>({
        id: 0,
        slug: '',
        name: '',
        description: '',
        created_at: '',
        updated_at: '',
        deleted_at: null,
        users_count: 0
    })

    async function fetchRole() {
        loading.value = true
        error.value = null

        await axios
            .get<RoleResponse>('/api/roles/r/' + toValue(slug))
            .then((response) => {
                role.value = response.data
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    watchEffect(() => {
        fetchRole()
    })

    return { role, error, loading, fetchRole }
}
