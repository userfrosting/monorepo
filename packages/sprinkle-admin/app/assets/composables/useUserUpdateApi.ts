import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiErrorResponse, ApiResponse } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import type {
    UserGroupRequest,
    UserPasswordRequest,
    UserRolesRequest,
    UserStatusRequest,
    UserVerificationRequest
} from '../interfaces'

export function useUserUpdateApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submit<T>(user_name: string, endpoint: string, formData: T) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .put<ApiResponse>('/api/users/u/' + user_name + '/' + endpoint, formData)
            .then((response) => {
                useAlertsStore().push({
                    ...response.data,
                    style: Severity.Success
                })

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

    function submitUserStatus(user_name: string, data: UserStatusRequest) {
        return submit(user_name, 'status', data)
    }

    function submitUserVerification(user_name: string, data: UserVerificationRequest) {
        return submit(user_name, 'verification', data)
    }

    function submitUserGroup(user_name: string, data: UserGroupRequest) {
        return submit(user_name, 'group', data)
    }

    function submitUserRoles(user_name: string, data: UserRolesRequest) {
        return submit(user_name, 'roles', data)
    }

    function submitUserPassword(user_name: string, data: UserPasswordRequest) {
        return submit(user_name, 'password', data)
    }

    return {
        submitUserStatus,
        submitUserVerification,
        submitUserGroup,
        submitUserRoles,
        submitUserPassword,
        apiLoading,
        apiError
    }
}
