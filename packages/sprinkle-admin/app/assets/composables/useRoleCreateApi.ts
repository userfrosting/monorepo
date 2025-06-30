import { ref } from 'vue'
import axios from 'axios'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { RoleCreateResponse, RoleCreateRequest } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation
// 'schema://requests/role/create.yaml'

/**
 * API Composable
 */
export function useRoleCreateApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    async function submitRoleCreate(data: RoleCreateRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<RoleCreateResponse>('/api/roles', data)
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

    return { submitRoleCreate, apiLoading, apiError }
}
