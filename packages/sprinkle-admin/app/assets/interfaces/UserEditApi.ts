import type { UserCreateRequest, UserCreateResponse } from './UserCreateApi'

/**
 * Interfaces - What the API expects and what it returns
 */
export type UserEditRequest = Omit<UserCreateRequest, 'group_id'>
export type UserEditResponse = UserCreateResponse
