import { ref, toValue, watchEffect } from 'vue'
import axios from 'axios'
import { type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { GroupResponse } from '../interfaces'

/**
 * API used to fetch a specific group.
 *
 * This interface is tied to the `GroupApi` API, accessed at the GET
 * `/api/groups/g/{slug}` endpoint and the `GroupApi` Typescript interface.
 *
 * This composable accept a {slug} to select the group. Any changes to the
 * {group} is watched and will trigger an update.
 *
 * Available ref:
 * - group: GroupApi
 * - error: AlertInterface | null
 * - loading: boolean
 * - fetchGroup(): void - Trigger a refresh of the data
 */
export function useGroupApi(slug: any) {
    const loading = ref(false)
    const error = ref<ApiErrorResponse | null>()
    const group = ref<GroupResponse>({
        id: 0,
        name: '',
        slug: '',
        description: '',
        icon: '',
        created_at: '',
        updated_at: '',
        deleted_at: null,
        users_count: 0
    })

    async function fetchGroup() {
        loading.value = true
        error.value = null

        await axios
            .get<GroupResponse>('/api/groups/g/' + toValue(slug))
            .then((response) => {
                group.value = response.data
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    watchEffect(() => {
        fetchGroup()
    })

    return { group, error, loading, fetchGroup }
}
