import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * API Interfaces - Defines the expected requests and responses for the API.
 *
 * These interfaces are associated with the `ForgetPasswordRequestAction`
 * and `ForgetPasswordSetPasswordAction` APIs, which are accessed via the
 * POST `/account/forgot-password/request` and POST `/account/forgot-password/
 * set-password` endpoints.
 */
export interface ForgotPasswordCodeRequest {
    email: string
}
export interface ForgotPasswordSetPasswordRequest {
    email: string
    password: string
    passwordc: string
    code: string
}
export type ForgotPasswordCodeResponse = ApiResponse
export type ForgotPasswordSetPasswordResponse = ApiResponse
