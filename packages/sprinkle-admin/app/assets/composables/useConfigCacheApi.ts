import { ref } from 'vue'
import axios from 'axios'
import {
    type AlertInterface,
    type ApiResponse,
    Severity
} from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

export function useConfigCacheApi() {
    const loading = ref(false)
    const error = ref<AlertInterface | null>()
    async function clearCache() {
        loading.value = true
        error.value = null
        return axios
            .post<ApiResponse>('/api/config/clear-cache')
            .then((response) => {
                useAlertsStore().push({
                    title: response.data.title,
                    description: response.data.description,
                    style: Severity.Success
                })

                return response.data
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { clearCache, loading, error }
}
