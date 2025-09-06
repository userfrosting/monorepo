import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type {
    ForgotPasswordCodeRequest,
    ForgotPasswordCodeResponse,
    ForgotPasswordSetPasswordRequest,
    ForgotPasswordSetPasswordResponse
} from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

/**
 * API Composable
 */
export function useForgotPasswordApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    /**
     * First step of the process. Ask the server to send a one time code to the
     * user by email.
     *
     * @param email The user email to send the one time code to.
     */
    async function requestCode(email: string) {
        apiLoading.value = true
        apiError.value = null
        const data: ForgotPasswordCodeRequest = {
            email: email
        }
        return axios
            .post<ForgotPasswordCodeResponse>('/account/forgot-password/request', data)
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

    /**
     * Second step of the password reset process. Ask the server to
     * verify the code entered by the user.
     *
     * @param email string - The email to validate.
     * @param code string - The verification code to validate.
     */
    async function setPassword(data: ForgotPasswordSetPasswordRequest) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .post<ForgotPasswordSetPasswordResponse>('/account/forgot-password/set-password', data)
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

    return { requestCode, setPassword, apiLoading, apiError }
}
