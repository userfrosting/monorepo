import { ref, toValue, watch } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import slug from 'limax'
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
 * Vue composable for Role CRUD operations.
 *
 * Endpoints:
 * - GET    /api/roles/r/{slug}  -> RoleResponse
 * - POST   /api/roles           -> RoleCreateResponse
 * - PUT    /api/roles/r/{slug}  -> RoleEditResponse
 * - DELETE /api/roles/r/{slug}  -> RoleDeleteResponse
 *
 * Reactive state:
 * - apiLoading: boolean
 * - apiError: ApiErrorResponse | null
 * - formData: RoleCreateRequest
 * - r$: validation state from Regle for formData
 *
 * Methods:
 * - fetchRole(slug: string): Promise<RoleResponse>
 * - createRole(data: RoleCreateRequest): Promise<void>
 * - updateRole(slug: string, data: RoleEditRequest): Promise<void>
 * - deleteRole(slug: string): Promise<void>
 * - resetForm(): void
 */
export function useRoleApi() {
    const defaultFormData = (): RoleCreateRequest => ({
        name: '',
        slug: '',
        description: ''
    })

    const slugLocked = ref<boolean>(true)
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

    watch(
        () => formData.value.name,
        (newName) => {
            if (slugLocked.value) {
                formData.value.slug = slug(newName)
            }
        }
    )

    return {
        fetchRole,
        createRole,
        updateRole,
        deleteRole,
        apiLoading,
        apiError,
        formData,
        r$,
        resetForm,
        slugLocked
    }
}
