import { ref } from 'vue'
import axios from 'axios'
import { type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { GroupsSprunjerResponse } from '../interfaces'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'

/**
 * API used to fetch a list of groups.
 *
 * This interface is tied to the `GroupsSprunjeAction` API, accessed at the
 * GET `/api/groups` endpoint and the `GroupsSprunjerResponse` Typescript
 * interface.
 *
 * This composable can be used to access a list of groups, for select purpose
 * for example. While it uses the Sprunjer on the backend, it doesn't filter
 * nor sort the data. The Sprunjer should be used directly for that.
 *
 * NOTE: This group list is not access controlled. It return all groups, so use
 * it wisely.
 */
export function useGroupsApi() {
    const loading = ref(false)
    const error = ref<AlertInterface | null>()
    const groups = ref<GroupInterface[]>([])

    async function updateGroups() {
        loading.value = true
        error.value = null

        await axios
            .get<GroupsSprunjerResponse>('/api/groups')
            .then((response) => {
                groups.value = response.data.rows
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { groups, error, loading, updateGroups }
}
