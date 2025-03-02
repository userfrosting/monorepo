import type { UserCreateRequest, UserCreateResponse } from './UserCreateApi'

/**
 * Interfaces - What the API expects and what it returns
 */
export interface UserEditRequest extends UserCreateRequest {}
export interface UserEditResponse extends UserCreateResponse {}
