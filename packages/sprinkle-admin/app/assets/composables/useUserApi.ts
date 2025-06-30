import { ref, toValue, watchEffect } from 'vue'
import axios from 'axios'
import type { ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { UserResponse } from '../interfaces'

/**
 * API used to fetch data about a specific user.
 *
 * This interface is tied to the `UserApi` API, accessed at the GET
 * `/api/users/u/{user_name}` endpoint and the `UserResponse` Typescript
 * interface.
 *
 * This composable accept a {user_name} to select the user. Any changes to the
 * {user_name} is watched and will trigger an update.
 *
 * Available ref:
 * - user: UserResponse
 * - error: AlertInterface | null
 * - loading: boolean
 * - fetchUser(): void - Trigger a refresh of the user data
 */
export function useUserApi(user_name: any) {
    const loading = ref(false)
    const error = ref<ApiErrorResponse | null>()
    const user = ref<UserResponse>({
        id: 0,
        user_name: '',
        first_name: '',
        last_name: '',
        full_name: '',
        email: '',
        avatar: '',
        flag_enabled: false,
        flag_verified: false,
        group_id: null,
        locale: '',
        created_at: '',
        updated_at: '',
        deleted_at: null,
        locale_name: '',
        group: null
    })

    async function fetchUser() {
        loading.value = true
        error.value = null

        await axios
            .get<UserResponse>('/api/users/u/' + toValue(user_name))
            .then((response) => {
                user.value = response.data
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    watchEffect(() => {
        fetchUser()
    })

    return { user, error, loading, fetchUser }
}
