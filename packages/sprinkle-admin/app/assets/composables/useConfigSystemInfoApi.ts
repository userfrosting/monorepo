import { ref } from 'vue'
import axios from 'axios'
import { type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { ConfigSystemInfoResponse } from '../interfaces'

export function useConfigSystemInfoApi() {
    const loading = ref(false)
    const error = ref<AlertInterface | null>()
    const data = ref<ConfigSystemInfoResponse>({
        frameworkVersion: '',
        phpVersion: '',
        database: {
            connection: '',
            name: '',
            type: '',
            version: ''
        },
        server: '',
        projectPath: '',
        sprinkles: {}
    })

    async function load() {
        loading.value = true
        error.value = null
        return axios
            .get<ConfigSystemInfoResponse>('/api/config/info')
            .then((response) => {
                data.value = response.data
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { data, load, loading, error }
}
