import axios from 'axios'
import { AlertStyle, type AlertInterface } from '@userfrosting/sprinkle-core/types'

// Actions
async function forgotPassword(email: String) {
    return axios
        .post<{ message: string }>('/account/forgot-password', email)
        .then((response) => {
            const error: AlertInterface = {
                description: response.data.message,
                style: AlertStyle.Success,
                closeBtn: true
            }

            return error
        })
        .catch((err) => {
            const error: AlertInterface = {
                ...{
                    description: 'An error as occurred',
                    style: AlertStyle.Danger,
                    closeBtn: true
                },
                ...err.response.data
            }

            throw error
        })
}

export default forgotPassword
