import axios from 'axios'
import { useAuthStore } from '../stores'
import { useCsrf } from '@userfrosting/sprinkle-core/composables'

/**
 * Axios Error Handler
 *
 * This composable sets up an Axios interceptor to handle errors globally using
 * the Alerts store. If a 401 error occurs, it will unset the user in the Auth
 * store.
 *
 * @see https://axios-http.com/docs/interceptors
 */
export const useAxiosInterceptor = () => {
    axios.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response.status === 401) {
                const authStore = useAuthStore()
                authStore.unsetUser()
                useCsrf().fetchCsrfToken()
            }

            return Promise.reject(error)
        }
    )
}
