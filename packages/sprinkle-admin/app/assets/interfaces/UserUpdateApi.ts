/** Request payload for the user status endpoint. */
export interface UserStatusRequest {
    flag_enabled: '0' | '1'
}

/** Request payload for the user verification endpoint. */
export interface UserVerificationRequest {
    flag_verified: '0' | '1'
}

/** Request payload for the user group endpoint. */
export interface UserGroupRequest {
    group_id: number
}

/** Request payload for the user roles endpoint. */
export interface UserRolesRequest {
    roles: number[]
}
