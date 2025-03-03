import { ref } from 'vue'
import axios from 'axios'
import { type AlertInterface, Severity } from '@userfrosting/sprinkle-core/interfaces'
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

    return { data, load, loading, error }
}
