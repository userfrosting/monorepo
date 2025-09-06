import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type {
    ResendVerificationRequest,
    ResendVerificationResponse,
    ValidateCodeRequest,
    ValidateCodeResponse
} from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation (Need new yaml file)

/**
 * API Composable
 */
export function useEmailVerificationApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<AlertInterface | null>(null)

    /**
     * First step of the verification process. Ask the server to send a
     * verification code by email to the user.
     *
     * @param email The user email to send the verification code to.
     */
    async function requestVerificationCode(email: string) {
        apiLoading.value = true
        apiError.value = null
        const data: ResendVerificationRequest = {
            email: email
        }

        return axios
            .post<ResendVerificationResponse>('/account/verify/request', data)
            .then((response): ResendVerificationResponse => {
                return response.data
            })
            .catch((err) => {
                apiError.value = err.response?.data ?? { description: err.message }
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
     */
    async function submitVerificationCode(email: string, code: string) {
        apiLoading.value = true
        apiError.value = null
        const data: ValidateCodeRequest = {
            email: email,
            code: code
        }

        return axios
            .post<ValidateCodeResponse>('/account/verify/email', data)
            .then((response): ValidateCodeResponse => {
                useAlertsStore().push({
                    ...{ style: Severity.Success },
                    ...response.data
                })

                return response.data
            })
            .catch((err) => {
                apiError.value = err.response?.data ?? { description: err.message }
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { requestVerificationCode, submitVerificationCode, apiLoading, apiError }
}
