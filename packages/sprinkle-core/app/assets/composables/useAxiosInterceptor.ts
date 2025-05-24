import axios from 'axios'
import { useAlertsStore } from '../stores/useAlertsStore'
import { Severity } from '../interfaces'

/**
 * Axios Error Handler
 *
 * This composable sets up an Axios interceptor to handle errors globally using
 * the Alerts store.
 *
 * @see https://axios-http.com/docs/interceptors
 */
export const useAxiosInterceptor = () => {
    axios.interceptors.response.use(
        (response) => response,
        (error) => {
            const alertStore = useAlertsStore()
            alertStore.push({
                title: error.response.data.title ?? error.response?.statusText,
                description: error.response?.data?.description ?? error.message,
                style: Severity.Danger
            })
            return Promise.reject(error)
        }
    )
}
