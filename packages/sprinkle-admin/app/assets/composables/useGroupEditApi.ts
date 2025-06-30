import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { GroupEditRequest, GroupEditResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/group/edit-info.yaml'

/**
 * API Composable
 */
export function useGroupEditApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function submitGroupEdit(slug: string, data: GroupEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<GroupEditResponse>('/api/groups/g/' + slug, data)
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

    return { submitGroupEdit, apiLoading, apiError }
}
