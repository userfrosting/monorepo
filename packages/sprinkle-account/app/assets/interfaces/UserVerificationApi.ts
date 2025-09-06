import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * API Interfaces - Defines the expected requests and responses for the API.
 *
 * These interfaces are associated with the `ResendVerificationAction` and
 * `Verify` APIs, which are accessed via the POST `/account/resend-verification`
 * and POST `/account/verify` endpoints.
 */
export interface ResendVerificationRequest {
    email: string
}
export interface ValidateCodeRequest {
    email: string
    code: string
}
export type ResendVerificationResponse = ApiResponse
export type ValidateCodeResponse = ApiResponse
