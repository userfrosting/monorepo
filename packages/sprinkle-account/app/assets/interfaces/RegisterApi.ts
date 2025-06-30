import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `RegisterAction` API, accessed at the
 * POST `/account/register` endpoint.
 */
export interface RegisterRequest {
    first_name: string
    last_name: string
    email: string
    user_name: string
    password: string
    passwordc: string
    locale: string
    captcha: string
    spiderbro: string
}

export interface RegisterResponse extends ApiResponse {}
