import { ref } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import {
    type ApiResponse,
    type ApiErrorResponse,
    Severity
} from '@userfrosting/sprinkle-core/interfaces'
import type { PasswordEditRequest } from '../interfaces'
import { useAlertsStore, useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/account-settings.yaml?raw'

/**
 * API Composable
 */
export function useUserPasswordEditApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)
    const passwordMinLength = ref<number>(0)
    const passwordMaxLength = ref<number>(0)
    const formData = ref<PasswordEditRequest>({
        passwordcheck: '',
        password: '',
        passwordc: ''
    })

    // Retrieve min/max password length from site settings and update validator
    // constraints
    const config = useConfigStore()
    passwordMinLength.value = config.get('site.password.length.min')
    passwordMaxLength.value = config.get('site.password.length.max')

    // TODO : Pass min/max to Regle (can't change defined regle, the message won't follow)
    // TODO : matches rules is not implemented

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function submitPasswordEdit(data: PasswordEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ApiResponse>('/account/settings', data)
            .then((response) => {
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

    return {
        submitPasswordEdit,
        apiLoading,
        apiError,
        formData,
        r$,
        minLength: passwordMinLength,
        maxLength: passwordMaxLength
    }
}
