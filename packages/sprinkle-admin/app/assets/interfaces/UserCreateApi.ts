import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Interfaces - What the API expects and what it returns
 */
export interface UserCreateRequest {
    user_name: string
    group_id: number | null
    first_name: string
    last_name: string
    email: string
    locale: string
}

export type UserCreateResponse = ApiResponse
