import { ref } from 'vue'
import axios from 'axios'
import {
    type AlertInterface,
    type ApiResponse,
    Severity
} from '@userfrosting/sprinkle-core/interfaces'

export function useConfigCacheApi() {
    const loading = ref(false)
    const error = ref<AlertInterface | null>()
    async function clearCache() {
        loading.value = true
        error.value = null
        return axios
            .post<ApiResponse>('/api/config/clear-cache')
            .then((response) => {
                return response.data.message
            })
            .catch((err) => {
                error.value = {
                    ...{
                        description: 'An error as occurred',
                        style: Severity.Danger,
                        closeBtn: true
                    },
                    ...err.response.data
                }

                throw error
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { clearCache, loading, error }
}
