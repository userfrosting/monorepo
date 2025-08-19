import { ref } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiResponse, AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { EmailEditRequest } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/account-email.yaml?raw'

/**
 * API Composable
 */
export function useUserEmailEditApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<AlertInterface | null>(null)
    const formData = ref<EmailEditRequest>({
        email: '',
        passwordcheck: ''
    })

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function submitEmailEdit(data: EmailEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ApiResponse>('/account/settings/email', data)
            .then((response) => {
                useAlertsStore().push({
                    ...{ style: Severity.Success },
                    ...response.data
                })

                return response.data
            })
            .catch((err) => {
                apiError.value = err.response.data
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { submitEmailEdit, apiLoading, apiError, formData, r$ }
}
