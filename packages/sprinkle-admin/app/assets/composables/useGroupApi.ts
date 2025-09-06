import { ref, toValue, watch } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import slug from 'limax'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type {
    GroupCreateRequest,
    GroupCreateResponse,
    GroupDeleteResponse,
    GroupEditRequest,
    GroupEditResponse,
    GroupResponse
} from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/group.yaml'

/**
 * Vue composable for Group CRUD operations.
 *
 * Endpoints:
 * - GET    /api/groups/g/{slug}  -> GroupResponse
 * - POST   /api/groups           -> GroupCreateResponse
 * - PUT    /api/groups/g/{slug}  -> GroupEditResponse
 * - DELETE /api/groups/g/{slug}  -> GroupDeleteResponse
 *
 * Reactive state:
 * - apiLoading: boolean
 * - apiError: ApiErrorResponse | null
 * - formData: GroupCreateRequest
 * - r$: validation state from Regle for formData
 *
 * Methods:
 * - fetchGroup(slug: string): Promise<GroupResponse>
 * - createGroup(data: GroupCreateRequest): Promise<void>
 * - updateGroup(slug: string, data: GroupEditRequest): Promise<void>
 * - deleteGroup(slug: string): Promise<void>
 * - resetForm(): void
 */
export function useGroupApi() {
    const defaultFormData = (): GroupCreateRequest => ({
        slug: '',
        name: '',
        description: '',
        icon: 'users'
    })

    const slugLocked = ref<boolean>(true)
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)
    const formData = ref<GroupCreateRequest>(defaultFormData())

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function fetchGroup(slug: string) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get<GroupResponse>('/api/groups/g/' + toValue(slug))
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

    async function createGroup(data: GroupCreateRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<GroupCreateResponse>('/api/groups', data)
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

    async function updateGroup(slug: string, data: GroupEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<GroupEditResponse>('/api/groups/g/' + slug, data)
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

    async function deleteGroup(slug: string) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .delete<GroupDeleteResponse>('/api/groups/g/' + slug)
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
        (name) => {
            if (slugLocked.value) {
                formData.value.slug = slug(name)
            }
        }
    )

    return {
        fetchGroup,
        createGroup,
        updateGroup,
        deleteGroup,
        apiLoading,
        apiError,
        formData,
        r$,
        resetForm,
        slugLocked
    }
}
