import { ref, toValue, watchEffect } from 'vue'
import axios from 'axios'
import { type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { PermissionResponse } from '../interfaces'

/**
 * API used to fetch data about a specific permission.
 *
 * This interface is tied to the `PermissionApi` API, accessed at the GET
 * `/api/permissions/p/{id}` endpoint and the `PermissionResponse` Typescript
 * interface.
 *
 * This composable accept a {id} to select the permission. Any changes to the
 * {id} is watched and will trigger an update.
 *
 * Available ref:
 * - permission: PermissionResponse
 * - error: AlertInterface | null
 * - loading: boolean
 * - fetchPermission(): void - Trigger a refresh of the user data
 */
export function usePermissionApi(id: any) {
    const loading = ref(false)
    const error = ref<ApiErrorResponse | null>()
    const permission = ref<PermissionResponse>({
        id: 0,
        slug: '',
        name: '',
        conditions: '',
        description: '',
        created_at: '',
        updated_at: '',
        deleted_at: null
    })

    async function fetchPermission() {
        loading.value = true
        error.value = null

        await axios
            .get<PermissionResponse>('/api/permissions/p/' + toValue(id))
            .then((response) => {
                permission.value = response.data
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    watchEffect(() => {
        fetchPermission()
    })

    return { permission, error, loading, fetchPermission }
}
