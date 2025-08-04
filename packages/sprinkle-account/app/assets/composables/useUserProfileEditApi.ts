import { ref } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore, useTranslator } from '@userfrosting/sprinkle-core/stores'
import type { ApiResponse, ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { ProfileEditRequest } from '../interfaces'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/profile-settings.yaml?raw'

/**
 * API Composable
 */
export function useUserProfileEditApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)
    const formData = ref<ProfileEditRequest>({
        first_name: '',
        last_name: '',
        locale: ''
    })

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function submitProfileEdit(data: ProfileEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ApiResponse>('/account/settings/profile', data)
            .then((response) => {
                // Reload the translator dictionary to reflect the user's language
                useTranslator().load()

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

    return { submitProfileEdit, apiLoading, apiError, formData, r$ }
}
