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

/**
 * API Composable
 */
export function useForgotPasswordApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    /**
     * First step of the process. Ask the server to send a one time code to the
     * user by email.
     *
     * @param email The user email to send the one time code to.
     *
     * @return {Promise} - The request success message given by the API. Throws an error
     * (AlertInterface) if the request failed.
     */
    async function requestCode(email: string): Promise<string> {
        apiLoading.value = true
        apiError.value = null
        const data: ForgotPasswordCodeRequest = {
            email: email
        }
        return axios
            .post<ForgotPasswordCodeResponse>('/account/forgot-password/request', data)
            .then((response): string => {
                return response.data.message
            })
            .catch((err) => {
                apiError.value = {
                    ...{
                        description: 'An error as occurred',
                        style: Severity.Danger,
                        closeBtn: true
                    },
                    ...err.response.data
                }

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
     *
     * @return {Promise} - A success message returned by the API. Throws an error
     * (AlertInterface) if the request failed.
     */
    async function setPassword(
        data: ForgotPasswordSetPasswordRequest
    ): Promise<ForgotPasswordSetPasswordResponse> {
        apiLoading.value = true
        apiError.value = null

        return axios
            .post<ForgotPasswordSetPasswordResponse>('/account/forgot-password/set-password', data)
            .then((response): ForgotPasswordSetPasswordResponse => {
                return {
                    message: response.data.message
                }
            })
            .catch((err) => {
                apiError.value = {
                    ...{
                        description: 'An error as occurred',
                        style: Severity.Danger,
                        closeBtn: true
                    },
                    ...err.response.data
                }

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { requestCode, setPassword, apiLoading, apiError }
}
