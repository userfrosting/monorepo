/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the user password update API, accessed at the
 * PUT `/api/users/u/{user_name}/password` endpoint.
 *
 * This api doesn't have a corresponding Response data interface. `ApiResponse`
 * is used as response interface.
 */
export interface UserPasswordRequest {
    password: string
    passwordc: string
}
