import type { UserInterface, FlattenPermissionsInterface } from './'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `LoginAction` API, accessed at the
 * POST `/account/login` endpoint.
 */
export interface LoginRequest {
    user_name: string
    password: string
}

export interface LoginResponse {
    user: UserInterface
    permissions: FlattenPermissionsInterface
    message: string
    redirect: string
}
