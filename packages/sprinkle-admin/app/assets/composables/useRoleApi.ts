import { ref, toValue } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type {
    RoleCreateResponse,
    RoleCreateRequest,
    RoleEditRequest,
    RoleEditResponse,
    RoleDeleteResponse,
    RoleResponse
} from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/role.yaml?raw'

/**
 * API Composable
 */
export function useRoleApi() {
    const defaultFormData = (): RoleCreateRequest => ({
        name: '',
        slug: '',
        description: ''
    })

    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)
    const formData = ref<RoleCreateRequest>(defaultFormData())

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function fetchRole(slug: string) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get<RoleResponse>('/api/roles/r/' + toValue(slug))
            .then((response) => {
                return response.data
            })
            .catch((err) => {
                apiError.value = err.response.data

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    async function createRole(data: RoleCreateRequest) {
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

    async function updateRole(slug: string, data: RoleEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<RoleEditResponse>('/api/roles/r/' + slug, data)
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

    async function deleteRole(slug: string) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .delete<RoleDeleteResponse>('/api/roles/r/' + slug)
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

    function resetForm() {
        formData.value = defaultFormData()
    }

    return {
        fetchRole,
        createRole,
        updateRole,
        deleteRole,
        apiLoading,
        apiError,
        formData,
        r$,
        resetForm
    }
}
