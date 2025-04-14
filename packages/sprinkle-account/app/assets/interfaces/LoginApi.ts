import type { UserDataInterface } from './'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `LoginAction` API, accessed at the
 * POST `/account/login` endpoint.
 */
export interface LoginRequest {
    user_name: string
    password: string
    rememberme?: boolean
}

export interface LoginResponse {
    user: UserDataInterface
    message: string
    redirect: string
}
