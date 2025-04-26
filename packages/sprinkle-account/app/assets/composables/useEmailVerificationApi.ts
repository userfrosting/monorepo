import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type {
    ResendVerificationResponse,
    ValidateCodeRequest,
    ValidateCodeResponse
} from '../interfaces'

// TODO : Add validation
// 'schema://requests/account-email.yaml'

/**
 * API Composable
 */
export function useEmailVerificationApi() {
    const apiLoading = ref<Boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    /**
     * First step of the verification process. Ask the server to send a
     * verification code by email to the user.
     *
     * @param email The user email to send the verification code to.
     *
     * @return {Promise} - The request success message given by the API. Throws an error
     * (AlertInterface) if the request failed.
     */
    async function resendVerification(email: string): Promise<string> {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<ResendVerificationResponse>('/account/verify/request', { email: email })
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
     * Second step of the verification process. Ask the server to
     * verify the code entered by the user.
     *
     * @param email string - The email to validate.
     * @param code string - The verification code to validate.
     *
     * @return {Promise} - A success message returned by the API. Throws an error
     * (AlertInterface) if the request failed.
     */
    async function submitVerificationCode(
        email: string,
        code: string
    ): Promise<ValidateCodeResponse> {
        apiLoading.value = true
        apiError.value = null
        const data: ValidateCodeRequest = {
            email: email,
            code: code
        }

        return axios
            .post<ValidateCodeResponse>('/account/verify/email', data)
            .then((response): ValidateCodeResponse => {
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

    return { resendVerification, submitVerificationCode, apiLoading, apiError }
}
